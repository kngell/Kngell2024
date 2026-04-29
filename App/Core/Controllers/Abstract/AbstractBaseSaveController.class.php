<?php

declare(strict_types=1);

abstract class AbstractBaseSaveController extends Controller
{
    use AjaxResponseTrait;

    public function __construct(
        protected SaveServiceInterface $saveService,
        FormCreatorService $frm,
        protected ValidatorInterface $validator,
        protected FileUploadFactory $uploader,
        protected FormDataHandlerService $formDataHandler,
    ) {
        $this->layout('admin');
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

    // ──────────────────────────────────────────────
    //  Persistence (unchanged internally)
    // ──────────────────────────────────────────────

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

    // ──────────────────────────────────────────────
    //  Response handlers — now delegating to trait
    // ──────────────────────────────────────────────

    private function handleSuccessfulSave(
        int $entityId,
        string $operationType,
        QueryResult $save,
        bool $anythingChanged,
        bool $isSkipped,
        ?string $redirectUrl = null,
    ): Response {
        $isAjax = $this->request->isAjax();
        $flashType = $anythingChanged ? FlashType::SUCCESS : FlashType::INFO;
        $finalRedirect = $redirectUrl ?: $this->navigationHistory->getRedirectUrl();

        $message = $this->saveService->getSuccessMessage($operationType, !$anythingChanged);

        if (!$isAjax && $isSkipped) {
            $message = ucfirst($this->saveService->getEntityName()) . ' info was already up to date.';
        }

        // Guard against stray output before JSON
        if ($isAjax && ob_get_level()) {
            ob_clean();
        }

        return $this->respondSuccess(
            isAjax: $isAjax,
            message: $message,
            redirect: $finalRedirect,
            flashType: $flashType,
            extraData: [
                $this->saveService->getEntityName() . '_id' => $entityId,
                'operation' => $operationType,
                'was_skipped' => !$anythingChanged,
            ],
        );
    }

    private function handleValidationError(
        array $data,
        FileUploadCompositeInterface $service,
        array $errors,
        array $paths,
    ): Response {
        $this->formDataHandler->storeFormData(
            $data,
            $service,
            $errors,
            $paths,
            $this->request->getRequestedUri(),
        );

        return $this->respondError(
            isAjax: $this->request->isAjax(),
            message: 'The form contains one or many errors.',
            redirect: $this->getErrorRedirectUrl($data),
            flashType: FlashType::DANGER,
            statusCode: HttpStatusCode::HTTP_UNPROCESSABLE_ENTITY,
            extraData: ['errors' => $errors],
        );
    }

    private function handleFileStorageFailure(
        FileUploadCompositeInterface $uploadService,
        array $formData,
    ): Response {
        $uploadService->cleanup();

        return $this->respondError(
            isAjax: $this->request->isAjax(),
            message: 'Failed to save files permanently.',
            redirect: $this->getErrorRedirectUrl($formData),
            statusCode: HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
        );
    }

    private function handleDatabaseError(Throwable $e, array $formData): Response
    {
        $this->logDatabaseError($e);

        $extraData = [];
        if ($_ENV['APP_ENV'] === 'development') {
            $extraData['debug_message'] = $e->getMessage();
        }

        return $this->respondError(
            isAjax: $this->request->isAjax(),
            message: 'A database error occurred while saving.',
            redirect: $this->getErrorRedirectUrl($formData),
            statusCode: HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR,
            extraData: $extraData,
        );
    }

    private function abortNoData(): Response
    {
        $entityName = $this->saveService->getEntityName();
        $redirectUrl = $this->navigationHistory->getRedirectUrl() ?? '/';

        return $this->respondError(
            isAjax: $this->request->isAjax(),
            message: "No {$entityName} data to save",
            redirect: $redirectUrl,
        );
    }

    // ──────────────────────────────────────────────
    //  Helpers
    // ──────────────────────────────────────────────

    /**
     * Builds the redirect URL string for error scenarios.
     * Extracted from the old errorRedirect() so the trait
     * receives a string instead of a Response.
     */
    private function getErrorRedirectUrl(array $formData): string
    {
        $entityId = $this->saveService->getEntityIdFromForm($formData);
        $entityName = $this->saveService->getEntityName();

        if ($entityId) {
            return "/admin/{$entityId}/{$entityName}-edit";
        }

        return "/admin/{$entityName}-add";
    }

    private function isRequestEmpty(array $data, array $additionalKeysToExclude = []): bool
    {
        return $this->formDataHandler->isEmptyData($data, $additionalKeysToExclude)
            && $this->request->getFiles()->isEmpty();
    }

    private function handleFileUploads(ValidationResult $result, array $webPaths): FileUploadCompositeInterface
    {
        $service = $this->uploader->create($this->request, $result->getErrors(), $webPaths);
        $service->setFormTemporaryWebPaths($webPaths);
        $service->proceed(false, true);
        return $service;
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
        $operation = $queryResult->getSqlOperation()->value;
        return $operation ?? 'unknown';
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