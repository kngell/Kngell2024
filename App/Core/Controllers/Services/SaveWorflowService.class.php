<?php

declare(strict_types=1);

final class SaveWorflowService
{
    public function __construct(
        private ValidatorInterface $validator,
        private FileUploadFactory $uploader,
        private FormDataHandlerService $formDataHandler,
        private EventDispatcherInterface $eventDispatcher,
        private NavigationHistoryService $navigationHistory,
    ) {
    }

    public function handle(
        Request $request,
        SaveServiceInterface $saveService,
    ): SaveResult {
        $expandedData = $request->getPost()->getAll();
        if ($this->isRequestEmpty($request, $expandedData)) {
            return $this->abortNoData($saveService);
        }

        $formData = $this->formDataHandler->prepareForValidation($expandedData);
        $webPaths = $this->formDataHandler->extractWebPathsFromForm($formData);

        $validationResult = $this->validator->validate(
            array_merge($formData, $request->getFiles()->all()),
            $saveService->getValidationRules(),
            $saveService->getModel(),
        );

        $uploadService = $this->handleFileUploads(
            request: $request,
            validationResult: $validationResult,
            webPaths: $webPaths,
        );

        $allErrors = array_merge(
            $validationResult->getErrors(),
            $uploadService->getErrors(),
        );

        if (!$validationResult->isValid() || !empty($uploadService->getErrors())) {
            return $this->handleValidationError(
                request: $request,
                saveService: $saveService,
                formData: $formData,
                uploadService: $uploadService,
                errors: $allErrors,
                webPaths: $webPaths,
            );
        }

        if (!$uploadService->makePermanent()) {
            return $this->handleFileStorageFailure(
                saveService: $saveService,
                uploadService: $uploadService,
                formData: $formData,
            );
        }

        return $this->persistEntity(
            request: $request,
            saveService: $saveService,
            formData: $formData,
            uploadService: $uploadService,
        );
    }

    private function persistEntity(
        Request $request,
        SaveServiceInterface $saveService,
        array $formData,
        FileUploadCompositeInterface $uploadService,
    ): SaveResult {
        $filePaths = $saveService->processFilePaths($formData, $uploadService);
        $formData = array_merge($formData, $filePaths);

        $model = $saveService->getModel();
        $em = $model->getEntityManager();

        $em->beginTransaction();

        try {
            $save = $model->save($formData);

            if (!$save->isSuccess()) {
                throw new RuntimeException($saveService->getEntityName() . ' save failed.');
            }

            $entityId = $this->getEntityIdFromSave($saveService, $save, $formData);
            $operationType = $this->getOperationTypeFromSave($save);
            $wasSkipped = $save->wasSkipped();
            $record = $model->getFromIdentityMap($entityId);
            $model->clearIdentityMap($entityId);

            $identifier = !is_null($record) ? [$record->getEntityKeyField() => $record->getEntityPrimarykeyValue()] : [];

            $eventDTO = EventDataDTO::from(
                eventName: strtolower($saveService->getEntityName()) . '.saved',
                entityId: $entityId,
                record: $record,
                identifier: $identifier,
                formData: $formData,
                publicId: $formData['public_id'] ?? null,
                operation: $operationType,
                wasSkipped: $save->wasSkipped(),
                media: $filePaths,
                modelData: $model->getEventData(),
                context: [
                    'is_new' => $operationType === 'insert',
                    'has_variations' => !empty($formData['variations']),
                    'has_price_change' => isset($formData['base_price']),
                    'block_type' => $formData['block_type'] ?? null,
                ],
            );
            $event = $saveService->buildSaveEvent(
                $eventDTO,
            );

            $this->eventDispatcher->notify($event);

            $em->commit();

            $this->formDataHandler->clearStoredFormData(
                $request->getRequestedUri(),
            );
            $mainTableChanged = $save->hasChanged();
            $listenersChanged = $event->hasDatabaseChanges();
            $anythingChanged = $mainTableChanged || $listenersChanged;

            $redirectUrl = $saveService->getRedirectUrl(
                entityId: $entityId,
                operationType: $operationType,
            );

            $message = $saveService->getSuccessMessage(
                operationType: $operationType,
                wasSkipped: !$anythingChanged,
            );
            $entity = $em->getEntity();

            if ($entity instanceof Entity) {
                $keyField = $entity->getEntityKeyField();
                if (!$entity->isInitialized($keyField)) {
                    $entity->__set($keyField, $entityId);
                }
            }

            // Preserve original form data values (e.g., obfuscated IDs like column_id)
            // before converting entity to array
            $originalFormDataKeys = ['column_id'];
            $preservedValues = [];
            foreach ($originalFormDataKeys as $key) {
                if (isset($formData[$key])) {
                    $preservedValues[$key] = $formData[$key];
                }
            }

            $formData = $entity->toFormArray();

            // Restore preserved values into the converted form data
            $formData = array_merge($formData, $preservedValues);

            return SaveResult::success(
                entityName: $saveService->getEntityName(),
                entityId: $entityId,
                operationType: $operationType,
                anythingChanged: $anythingChanged,
                wasSkipped: $wasSkipped,
                message: $message,
                redirectUrl: $redirectUrl,
                extraData: [
                    $saveService->getEntityName() . '_id' => $entityId,
                    'operation' => $operationType,
                    'was_skipped' => $wasSkipped,
                    'anything_changed' => $anythingChanged,
                    'form_data' => $formData,
                ],
            );
        } catch (Throwable $e) {
            $em->rollback();
            $uploadService->cleanupPermanentFiles();

            $this->logDatabaseError($e);

            $extraData = [];

            if (($_ENV['APP_ENV'] ?? '') === 'development') {
                $extraData['debug_message'] = $e->getMessage();
            }

            return SaveResult::databaseError(
                entityName: $saveService->getEntityName(),
                redirectUrl: $saveService->getErrorRedirectUrl($formData),
                exception: $e,
                extraData: $extraData,
            );
        }
    }

    private function handleValidationError(
        Request $request,
        SaveServiceInterface $saveService,
        array $formData,
        FileUploadCompositeInterface $uploadService,
        array $errors,
        array $webPaths,
    ): SaveResult {
        $this->formDataHandler->storeFormData(
            $formData,
            $uploadService,
            $errors,
            $webPaths,
            $request->getRequestedUri(),
        );

        return SaveResult::validationError(
            entityName: $saveService->getEntityName(),
            errors: $errors,
            redirectUrl: $saveService->getErrorRedirectUrl($formData),
        );
    }

    private function handleFileStorageFailure(
        SaveServiceInterface $saveService,
        FileUploadCompositeInterface $uploadService,
        array $formData,
    ): SaveResult {
        $uploadService->cleanup();

        return SaveResult::fileStorageError(
            entityName: $saveService->getEntityName(),
            redirectUrl: $saveService->getErrorRedirectUrl($formData),
        );
    }

    private function abortNoData(SaveServiceInterface $saveService): SaveResult
    {
        $entityName = $saveService->getEntityName();
        $redirectUrl = $this->navigationHistory->getRedirectUrl() ?? '/';

        return SaveResult::noData(
            entityName: $entityName,
            message: "No {$entityName} data to save",
            redirectUrl: $redirectUrl,
        );
    }

    private function handleFileUploads(
        Request $request,
        ValidationResult $validationResult,
        array $webPaths,
    ): FileUploadCompositeInterface {
        $uploadService = $this->uploader->create(
            $request,
            $validationResult->getErrors(),
            $webPaths,
        );

        // $uploadService->setFormTemporaryWebPaths($webPaths);
        $uploadService->proceed(false, true);

        return $uploadService;
    }

    private function isRequestEmpty(
        Request $request,
        array $data,
        array $additionalKeysToExclude = [],
    ): bool {
        return $this->formDataHandler->isEmptyData($data, $additionalKeysToExclude)
            && $request->getFiles()->isEmpty();
    }

    private function getEntityIdFromSave(
        SaveServiceInterface $saveService,
        QueryResult $queryResult,
        array $formData,
    ): int {
        $id = $queryResult->getLastInsertId() ?: $queryResult->getLastUpdateId();

        if (!$id) {
            $entityId = $saveService->getEntityIdFromForm($formData);

            if ($entityId) {
                return $entityId;
            }
        }

        if (!$id) {
            throw new RuntimeException(
                'Could not determine ' . $saveService->getEntityName() . ' ID for persistence.',
            );
        }

        return (int) $id;
    }

    private function getOperationTypeFromSave(QueryResult $queryResult): string
    {
        return $queryResult->getSqlOperation()->value ?? 'unknown';
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
