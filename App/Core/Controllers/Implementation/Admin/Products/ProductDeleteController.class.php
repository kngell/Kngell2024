<?php

declare(strict_types=1);

class ProductDeleteController extends Controller
{
    public function __construct(
        private ProductModel $productModel,
        private ProductDeleteService $deleteService,
        private ProductDeleteValidator $validator,
        FormCreatorService $frm,
        private HtmlTemplatePathInterface $templatePath,
    ) {
        $this->layout('admin');
        $this->frm = $frm;
    }

    public function confirm(): Response|string
    {
        $isAjax = $this->request->isAjax();

        if ($isAjax) {
            $productId = $this->request->getPost()->get('public_id', '');
            if (!$productId) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'No product selected',
                    'type' => 'warning',
                    'redirect' => '/admin/product-list',
                ], HttpStatusCode::HTTP_BAD_REQUEST);
            }

            $validationResult = $this->validator->validate($productId);
            if (!$validationResult->isValid()) {
                return new JsonResponse([
                    'success' => false,
                    'error' => $validationResult->getErrorMessage(),
                    'type' => 'danger',
                    'redirect' => '/admin/product-list',
                ], HttpStatusCode::HTTP_BAD_REQUEST);
            }

            $productData = [
                'product_id' => $productId,
                'product_name' => $validationResult->getProductName(),
                'sku' => $validationResult->getProductSku(),
                'stockQuantity' => $validationResult->getStockQuantity(),
                'warnings' => $validationResult->getWarnings(),
                'image' => $validationResult->getMainImage(),
                'timestamp' => time(),
            ];

            // Store in flash (will be retrieved in delete() method)
            $this->flash->addData('product_delete_data', $productData);
        }

        $productData = $this->flash->peekData('product_delete_data');

        if (!$productData) {
            if ($isAjax) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Deletion session expired. Please select the product again.',
                    'type' => 'warning',
                    'redirect' => '/admin/product-list',
                ], HttpStatusCode::HTTP_BAD_REQUEST);
            }

            $this->flash->add('Deletion session expired. Please select the product again.', FlashType::WARNING);
            return $this->redirect('/admin/product-list');
        }

        $this->pageTitle('Confirm Product Deletion');
        $confirm = new ProductDeletionDecorator(
            $this,
            'product-delete/delete',
            $productData,
            $this->templatePath,
        );

        $pageData = $confirm->page();

        if ($isAjax) {
            if (empty($pageData['confirmDeletionModal'])) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Failed to generate deletion confirmation modal',
                    'type' => 'danger',
                    'redirect' => '/admin/product-list',
                ], HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR);
            }

            return new JsonResponse(array_merge(
                ['success' => true],
                $pageData,
            ));
        } else {
            // For non-AJAX, render the full page
            return $this->render(
                'admin/confirm-deletion',
                $pageData,
            );
        }
    }

    public function delete(): Response
    {
        $confirmation = $this->request->getPost()->get('confirm_delete', 'no');
        $deleteOption = $this->request->getPost()->get('delete_option', 'archive');

        $isAjax = $this->request->isAjax();

        if ($confirmation !== 'on') {
            if ($isAjax) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Product deletion cancelled.',
                    'type' => 'info',
                    'redirect' => '/admin/product-list',
                ], HttpStatusCode::HTTP_OK);
            }

            $this->flash->add('Product deletion cancelled.', FlashType::INFO);
            return $this->redirect('/admin/product-list');
        }

        $productData = $this->flash->getData('product_delete_data');

        if (!$productData) {
            if ($isAjax) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Deletion session expired. Please try again.',
                    'type' => 'warning',
                    'redirect' => '/admin/product-list',
                ], HttpStatusCode::HTTP_BAD_REQUEST);
            }

            $this->flash->add('Deletion session expired. Please try again.', FlashType::WARNING);
            return $this->redirect('/admin/product-list');
        }

        $productId = $productData['product_id'];

        // Re-validate before proceeding (product might have changed)
        $validationResult = $this->validator->validate($productId);
        if (!$validationResult->isValid()) {
            if ($isAjax) {
                return new JsonResponse([
                    'success' => false,
                    'error' => $validationResult->getErrorMessage(),
                    'type' => 'danger',
                    'redirect' => '/admin/product-list',
                ], HttpStatusCode::HTTP_BAD_REQUEST);
            }

            $this->flash->add($validationResult->getErrorMessage(), FlashType::DANGER);
            $this->flash->getData('product_delete_data');
            return $this->redirect('/admin/product-list');
        }

        // Check if product is already soft deleted AND user chose "archive" (soft delete)
        if ($validationResult->isSoftDelete() && $deleteOption === 'archive') {
            $productName = $validationResult->getProductName() ?? 'Unknown Product';
            $message = sprintf('Product "%s" is already archived.', $productName);

            if ($isAjax) {
                return new JsonResponse([
                    'success' => false,
                    'error' => $message,
                    'type' => 'info',
                    'redirect' => '/admin/product-list',
                    'data' => [
                        'product_name' => $productName,
                        'was_skipped' => true,
                        'skip_reason' => 'Product was already archived',
                        'deletion_type' => 'archive',
                    ],
                ], HttpStatusCode::HTTP_OK);
            }

            $this->flash->add($message, FlashType::INFO);
            $this->flash->getData('product_delete_data');
            return $this->redirect('/admin/product-list');
        }

        $em = $this->productModel->getEntityManager();
        $em->beginTransaction();

        try {
            // Pass the deletion option to the service
            $result = $this->deleteService->deleteProduct($productId, $this->eventManager, $deleteOption);

            if ($result->isSuccess()) {
                $em->commit();

                if ($result->wasSkipped()) {
                    $message = $result->getSkipReason() === 'Product was already soft deleted'
                        ? sprintf('Product "%s" was already archived.', $result->getProductName())
                        : sprintf('Product "%s" - %s', $result->getProductName(), $result->getSkipReason());
                    $type = 'info';
                } else {
                    $deletionType = ($deleteOption === 'permanent') ? 'permanently deleted' : 'archived';
                    $message = sprintf('Product "%s" %s successfully.', $result->getProductName(), $deletionType);
                    $type = 'success';
                    if (method_exists($this, 'logDeletionActivity')) {
                        $this->logDeletionActivity($productId, $result->getProductName(), $deleteOption);
                    }
                }

                if ($isAjax) {
                    return new JsonResponse([
                        'success' => true,
                        'message' => $message,
                        'type' => $type,
                        'redirect' => '/admin/product-list',
                        'data' => [
                            'product_name' => $result->getProductName(),
                            'was_skipped' => $result->wasSkipped(),
                            'skip_reason' => $result->wasSkipped() ? $result->getSkipReason() : null,
                            'deletion_type' => $deleteOption,
                            'deletion_action' => ($deleteOption === 'permanent') ? 'permanent' : 'archive',
                        ],
                    ], HttpStatusCode::HTTP_OK);
                }

                $this->flash->add($message, $type === 'success' ? FlashType::SUCCESS : FlashType::INFO);
            } else {
                $em->rollback();

                if ($isAjax) {
                    return new JsonResponse([
                        'success' => false,
                        'error' => $result->getErrorMessage(),
                        'type' => 'danger',
                        'redirect' => '/admin/product-list',
                        'error_details' => $result->getErrorDetails(),
                    ], HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR);
                }

                $this->flash->add($result->getErrorMessage(), FlashType::DANGER);
                error_log('Product deletion failed: ' . json_encode($result->getErrorDetails()));
            }
        } catch (Exception $e) {
            $em->rollback();
            error_log('Product deletion exception: ' . $e->getMessage());

            if ($isAjax) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'An unexpected error occurred while deleting the product.',
                    'type' => 'danger',
                    'redirect' => '/admin/product-list',
                    'exception' => $e->getMessage(),
                ], HttpStatusCode::HTTP_INTERNAL_SERVER_ERROR);
            }

            $this->flash->add('An unexpected error occurred while deleting the product.', FlashType::DANGER);
        } finally {
            $this->flash->getData('product_delete_data');
        }

        if (!$isAjax) {
            return $this->redirect('/admin/product-list');
        }

        return new JsonResponse([
            'success' => true,
            'message' => 'Operation completed',
            'redirect' => '/admin/product-list',
        ], HttpStatusCode::HTTP_OK);
    }

    /**
     * Optional: Add this method if you need to log deletion activity.
     */
    private function logDeletionActivity(string $productId, string $productName): void
    {
        // Implement your logging logic here
        // Example:
        // $this->activityLogger->log('product_deleted', [
        //     'product_id' => $productId,
        //     'product_name' => $productName,
        //     'user_id' => $this->auth->getUserId(),
        //     'timestamp' => time(),
        // ]);
    }
}