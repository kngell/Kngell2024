<?php

declare(strict_types=1);

abstract class AbstractBaseSaveController extends Controller
{
    protected const string LAYOUT = 'admin';

    public function __construct(
        protected SaveServiceInterface $saveService,
        FormCreatorService $frm,
        protected ValidatorInterface $validator,
        protected FileUploadFactory $uploader,
        protected FormDataHandlerService $formDataHandler,
    ) {
        $this->layout(static::LAYOUT);
        $this->frm = $frm;
    }

    final public function index(): Response
    {
        $expandedData = $this->request->getPost()->getAll();
        if ($this->isRequestEmpty($expandedData)) {
            return $this->abortNoData();
        }

        $formData = $this->formDataHandler->prepareForValidation($expandedData);
        $webPaths = $this->formDataHandler->extractWebPathsFromForm($formData);

        $result = $this->validator->validate(
            array_merge($formData, $this->request->getFiles()->all()),
            $this->saveService->getValidationRules(),
            $this->saveService->getModel(),
        );

        $uploadService = $this->handleFileUploads($result, $webPaths);
        $allErrors = array_merge($result->getErrors(), $uploadService->getErrors());

        if (!$result->isValid() || !empty($uploadService->getErrors())) {
            return $this->handleValidationError($formData, $uploadService, $allErrors, $webPaths);
        }

        if (!$uploadService->makePermanent()) {
            return $this->handleFileStorageFailure($uploadService, $formData);
        }

        return $this->persistEntity($formData, $uploadService);
    }

    abstract protected function getEntitySpecificPageTitle(): string;

    private function persistEntity(array $formData, UploadService $uploadService): Response
    {
        $filePaths = $this->saveService->processFilePaths($formData, $uploadService);
        $formData = array_merge($formData, $filePaths);

        $model = $this->saveService->getModel();
        $em = $model->getEntityManager();
        $em->beginTransaction();

        try {
            $save = $model->save($formData);
            if (!$save->isSuccess()) {
                throw new RuntimeException($this->saveService->getEntityName() . ' save failed.');
            }

            $entityId = $this->getEntityIdFromSave($save, $formData);
            $operationType = $this->getOperationTypeFromSave($save);
            $isSkipped = $save->wasSkipped();

            $eventData = $this->saveService->buildEventData(
                $formData,
                $filePaths,
                $operationType,
                $entityId,
                $isSkipped,
                $model->getEventData(),
            );

            $eventClass = $this->saveService->getEventClass();
            $eventName = $this->saveService->getEntityName() . '.saved';

            $event = $this->eventManager->notify(
                new $eventClass($eventName, null, $eventData),
                null,
            );

            $em->commit();

            $this->formDataHandler->clearStoredFormData($this->request->getRequestedUri());

            $mainTableChanged = $save->hasChanged();
            $listenersChanged = $event->hasDatabaseChanges();
            $anythingChanged = $mainTableChanged || $listenersChanged;

            return $this->handleSuccessfulSave(
                entityId: $entityId,
                operationType: $operationType,
                save: $save,
                anythingChanged: $anythingChanged,
                isSkipped: $isSkipped,
                redirectUrl: $this->saveService->getRedirectUrl($entityId, $operationType),
            );
        } catch (Throwable $e) {
            $em->rollback();
            $uploadService->cleanupPermanentFiles();

            return $this->handleDatabaseError($e, $formData);
        }
    }

    private function handleSuccessfulSave(
        int $entityId,
        string $operationType,
        QueryResult $save,
        bool $anythingChanged,
        bool $isSkipped,
        ?string $redirectUrl = null,
    ): Response {
        $message = $this->saveService->getSuccessMessage($operationType, !$anythingChanged);

        if ($this->request->isAjax()) {
            if (ob_get_level()) {
                ob_clean();
            }
            return $this->jsonResponse([
                'success' => true,
                'message' => $message,
                $this->saveService->getEntityName() . '_id' => $entityId,
                'operation' => $operationType,
                'redirect' => $redirectUrl,
                'was_skipped' => !$anythingChanged,
                'type' => $anythingChanged ? 'success' : 'info',
            ]);
        }

        if ($isSkipped) {
            $this->flash->add(
                ucfirst($this->saveService->getEntityName()) . ' info was already up to date.',
                FlashType::INFO,
            );
        } else {
            $this->flash->add($message, FlashType::SUCCESS);
        }

        $finalRedirect = $redirectUrl ?: $this->navigationHistory->getRedirectUrl();
        return $this->redirect($finalRedirect);
    }

    private function isRequestEmpty(array $data, array $additionalKeysToExclude = []): bool
    {
        return $this->formDataHandler->isEmptyData($data, $additionalKeysToExclude) && $this->request->getFiles()->isEmpty();
    }

    private function handleFileUploads(ValidationResult $result, array $webPaths): FileUploadCompositeInterface
    {
        $service = $this->uploader->create($this->request, $result->getErrors(), $webPaths);
        $service->setFormTemporaryWebPaths($webPaths);
        $service->proceed(false, true);
        return $service;
    }

    private function handleValidationError(array $data, $service, array $errors, array $paths): Response
    {
        $this->formDataHandler->storeFormData(
            $data,
            $service,
            $errors,
            $paths,
            $this->request->getRequestedUri(),
        );

        if ($this->request->isAjax()) {
            return new JsonResponse([
                'success' => false,
                'error' => 'The form contains one or many errors.',
                'errors' => $errors,
                'type' => 'error',
            ], HttpStatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->flash->add('The Form Contains one or many errors.', FlashType::DANGER);
        return $this->errorRedirect($data);
    }

    private function handleFileStorageFailure($uploadService, array $formData): Response
    {
        $uploadService->cleanup();

        if ($this->request->isAjax()) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Failed to save files permanently.',
                'type' => 'error',
            ], HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->flash->add('Failed to save files permanently.', FlashType::DANGER);
        return $this->errorRedirect($formData);
    }

    private function handleDatabaseError(Throwable $e, array $formData): Response
    {
        $this->logDatabaseError($e);

        if ($this->request->isAjax()) {
            return new JsonResponse([
                'success' => false,
                'error' => 'A database error occurred while saving.',
                'message' => $_ENV['APP_ENV'] === 'development' ? $e->getMessage() : null,
                'type' => 'error',
            ], HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR);
        }

        $this->flash->add('A database error occurred while saving.', FlashType::DANGER);
        return $this->errorRedirect($formData);
    }

    private function abortNoData(): Response
    {
        $entityName = $this->saveService->getEntityName();

        if ($this->request->isAjax()) {
            return $this->jsonResponse([
                'success' => false,
                'error' => "No {$entityName} data to save",
                'type' => 'error',
            ]);
        }

        $this->flash->add("No {$entityName} data to save", FlashType::DANGER);
        $redirectUrl = $this->navigationHistory->getRedirectUrl();
        return $this->redirect($redirectUrl ?? '/');
    }

    private function errorRedirect(array $formData): Response
    {
        $entityId = $this->saveService->getEntityIdFromForm($formData);
        $entityName = $this->saveService->getEntityName();

        if ($entityId) {
            return $this->redirect("/admin/{$entityId}/{$entityName}-edit");
        }

        return $this->redirect("/admin/{$entityName}-add");
    }

    private function getEntityIdFromSave(QueryResult $queryResult, array $formData): int
    {
        $id = $queryResult->getLastInsertId() ?: $queryResult->getLastUpdateId();

        if (!$id) {
            $entityId = $this->saveService->getEntityIdFromForm($formData);
            if ($entityId) {
                return $entityId;
            }
        }

        if (!$id) {
            throw new RuntimeException(
                'Could not determine ' . $this->saveService->getEntityName() . ' ID for persistence.',
            );
        }

        return (int) $id;
    }

    private function getOperationTypeFromSave(QueryResult $queryResult): string
    {
        if ($queryResult->getLastInsertId()) {
            return 'insert';
        }

        if ($queryResult->getLastUpdateId()) {
            return 'update';
        }

        return $queryResult->getAffectedRows() > 0 ? 'update' : 'unknown';
    }

    private function logDatabaseError(Throwable $e): void
    {
        error_log('=== DATABASE ERROR ===');
        error_log('Message: ' . $e->getMessage());
        error_log('Code: ' . $e->getCode());
        error_log('File: ' . $e->getFile() . ':' . $e->getLine());
        error_log('Trace: ' . $e->getTraceAsString());

        if (str_contains($e->getMessage(), 'Cannot Convert')) {
            error_log('!!! CHARSET ENCODING ERROR !!!');
        }
    }
}