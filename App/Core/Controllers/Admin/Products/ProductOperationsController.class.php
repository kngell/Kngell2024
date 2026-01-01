<?php

declare(strict_types=1);

class ProductOperationsController extends Controller
{
    private const string LAYOUT = 'admin';

    public function __construct(
        private ProductModel $product,
        ProductFormCreator $frm,
        private ValidatorInterface $validator,
        private FileProcessorRegistry $processor,
        private FileMoverService $mover,
        private FormDataHandlerService $formDataHandler,
    ) {
        $this->layout(self::LAYOUT);
        $this->frm = $frm;
    }

    public function save(): Response
    {
        $expandedData = $this->request->getPost()->getAll();
        $hasAnyFile = $this->request->getFiles()->hasAnyFiles();

        if (empty($expandedData) && !$hasAnyFile) {
            $this->flash->add('Cannot Add the Product. There\'s no product data to save', FlashType::DANGER);
            return $this->redirect('/admin/product-list');
        }

        // Use the handler for all form data processing
        $formData = $this->formDataHandler->prepareForValidation($expandedData);
        $webPaths = $this->formDataHandler->extractWebPathsFromForm($formData);

        $files = $this->request->getFiles()->all();
        $validationData = array_merge($formData, $files);

        $result = $this->validator->validate($validationData, 'productRules', $this->product);

        $uploadService = UploadService::createFromRequest(
            $this->processor,
            $this->mover,
            $this->formDataHandler->getMetadataService(),
            $this->request,
            $result->getErrors(),
        );

        $temporaryMode = !$result->isValid();
        $uploadService->proceed(false, $temporaryMode);
        $uploadService->setFormTemporaryWebPaths($webPaths);

        $fileErrors = $uploadService->getErrors();
        $allErrors = array_merge($result->getErrors(), $fileErrors);

        if (!$result->isValid() || !empty($fileErrors)) {
            $this->formDataHandler->storeFormData(
                $formData,
                $uploadService,
                $allErrors,
                $webPaths,
                $this->request->getRequestedUri(),
            );

            $this->flash->add('The Form Contains one or many errors.', FlashType::DANGER);
            return $this->redirect('/admin/product-add');
        }

        if (!$uploadService->makePermanent()) {
            $uploadService->cleanup();
            $this->flash->add('Failed to save files permanently.', FlashType::DANGER);
            return $this->redirect('/admin/product-add');
        }

        $filePaths = [
            'main_image' => $uploadService->getFilePath('main_image[]'),
            'main_video' => $uploadService->getFilePath('main_video'),
            'img_gallery' => $uploadService->getMultiFilePaths('img_gallery[]'),
        ];

        $formData = array_merge($formData, $filePaths);
        $save = $this->product->save($formData);

        if ($save->isSuccess()) {
            $productId = $this->getProductIdFromSave($save);
            $operationType = $this->getOperationTypeFromSave($save);

            $event = new ProductSaveEvent(
                'product.saved',
                null,
                [
                    'product_id' => $productId,
                    'form_data' => $formData,
                    'uploaded_media' => $filePaths,
                    'media_metadata' => $uploadService->getFileMetadata(),
                    'operation_type' => $operationType,
                    'last_insert_id' => $save->getLastInsertId(),
                    'last_update_id' => $save->getLastUpdateId(),
                    'affected_rows' => $save->getAffectedRows(),
                ],
            );
            $this->eventManager->notify($event, null);

            $uploadService->cleanupOldTempFiles();
            $this->formDataHandler->clearStoredFormData($this->request->getRequestedUri());

            // Get appropriate message
            $message = $this->getSuccessMessage($operationType, $save);
            $this->flash->add($message, FlashType::SUCCESS);

            // Redirect
            if ($productId) {
                return $this->redirect("/admin/{$productId}/product-show");
            }

            return $this->redirect('/admin/products');
        }
        $uploadService->cleanupPermanentFiles();
        $uploadService->cleanup();

        $this->flash->add('Failed to create product due to a database error.', FlashType::DANGER);
        return $this->redirect('/admin/product-add');
    }

    private function getProductIdFromSave(QueryResult $queryResult): null|int
    {
        if ($lastInsertId = $queryResult->getLastInsertId()) {
            return (int) $lastInsertId;
        }
        if ($lastUpdateId = $queryResult->getLastUpdateId()) {
            return (int) $lastUpdateId;
        }

        return null;
    }

    private function getOperationTypeFromSave(QueryResult $queryResult): string
    {
        if ($queryResult->getLastInsertId()) {
            return 'insert';
        }

        if ($queryResult->getLastUpdateId()) {
            return 'update';
        }

        // Fallback based on affected rows
        return $queryResult->getAffectedRows() > 0 ? 'update' : 'unknown';
    }

    private function getSuccessMessage(string $operationType, QueryResult $save): string
    {
        return match($operationType) {
            'insert' => 'The product has been created successfully',
            'update' => $save->getAffectedRows() > 0
                ? 'The product has been updated successfully'
                : 'No changes were made to the product',
            default => 'Product operation completed',
        };
    }
}