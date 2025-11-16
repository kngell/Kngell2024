<?php

declare(strict_types=1);

class ProductController extends Controller
{
    use UploadTrait;

    private const string LAYOUT = 'admin';

    public function __construct(
        private ProductModel $product,
        ProductFormCreator $frm,
        private ValidatorInterface $validator,
        private FileProcessorRegistry $processor,
        private FileMoverService $mover,
    ) {
        $this->layout(self::LAYOUT);
        $this->frm = $frm;
    }

    public function create(): Response
    {
        $expandedData = $this->request->getPost()->getAll();
        if (empty($expandedData)) {
            $this->flash->add('Cannot Add the Product. There\'s no product data to save');
            return $this->redirect('/admin/product-add');
        }
        $formData = $this->prepareForValidation($expandedData);

        $webPaths = $this->extractWebPathsFromForm($formData);
        $files = $this->request->getFiles()->all();
        $validationData = array_merge($formData, $files);

        $result = $this->validator->validate($validationData, 'productRules', $this->product);

        $imageUpload = UploadService::createFromRequest(
            $this->processor,
            $this->mover,
            $this->request,
            $result->getErrors(),
        );

        $temporaryMode = !$result->isValid();
        $imageUpload->proceed(false, $temporaryMode);
        $fileErrors = $imageUpload->getErrors();

        if (!$result->isValid() || !empty($fileErrors)) {
            $errors = array_merge($result->getErrors(), $fileErrors);
            $this->storeFormDataInSession($formData, $imageUpload, $errors, $webPaths);
            $this->flash->add('The Form Contains one or many errors.', FlashType::DANGER);
            return $this->redirect('/admin/product-add');
        }

        $imageUpload->setFormTemporaryWebPaths($webPaths);

        $permanentResult = $imageUpload->makePermanent();

        if ($permanentResult === false) {
            $imageUpload->cleanup();
            $this->flash->add('Failed to save files permanently.', FlashType::DANGER);
            return $this->redirect('/admin/product-add');
        }

        // Clear session data after successful permanent file save
        $formKey = 'form_' . md5('product/create');
        $this->session->delete($formKey . '_files');
        $this->session->delete($formKey . '_values');
        $this->session->delete($formKey . '_errors');

        // 2. Prepare data for Model Save
        $formData['main_image'] = $imageUpload->getFilePath('main_image[]');
        $formData['main_video'] = $imageUpload->getFilePath('main_video');

        $insert = $this->product->save($formData);

        if ($insert->isSuccess()) {
            $newProductId = $insert->getLastInsertId();
            $mediaInfo = [
                'main_image' => $imageUpload->getFilePath('main_image[]'),
                'main_video' => $imageUpload->getFilePath('main_video'),
                'img_gallery' => $imageUpload->getMultiFilePaths('img_gallery[]'),
            ];

            $event = new ProductCreationEvent(
                'product.created',
                null,
                [
                    'product_id' => $newProductId,
                    'uploaded_media' => $mediaInfo,
                    'form_data' => $formData,
                ],
            );
            $this->eventManager->notify($event, null);
            $imageUpload->cleanupOldTempFiles();
            $this->flash->add('The product has been created successfully');
            return $this->redirect("/admin/{$newProductId}/show");
        }

        $imageUpload->cleanupPermanentFiles();
        $imageUpload->cleanup();

        $this->flash->add('Failed to create product due to a database error.', FlashType::DANGER);
        return $this->redirect('/admin/product-add');
    }

    public function webPathExists(array $formData, array $files): bool
    {
        foreach ($files as $field => $files) {
            if (str_contains($field, '[')) {
                $field = explode('[', $field)[0];
            }
            if (array_key_exists('web_path__' . $field, $formData)) {
                return true;
                break;
            }
        }
        return false;
    }

    /**
     * Enhanced method for edit/update with proper form data handling.
     */
    // public function update(int $id): Response
    // {
    //     $expandedData = $this->request->getPost()->getAll();
    //     $formData = $this->prepareForValidation($expandedData);

    //     // Get existing product data
    //     $existingProduct = $this->product->first([$id])->asObject();

    //     if (!$existingProduct) {
    //         $this->flash->add('Product not found', FlashType::DANGER);
    //         return $this->redirect('/admin/products');
    //     }

    //     // Merge with existing data (useful for partial updates)
    //     $mergedData = ArrayUtils::mergeFormData($existingProduct->toArray(), $formData);

    //     $result = $this->validator->validate($mergedData, 'productRules');
    //     $imageUpload->proceed(false);

    //     $errors = array_merge($result->getErrors(), $imageUpload->getErrors());

    //     if (!empty($errors)) {
    //         $formKey = 'form_' . md5("product/update/{$id}");

    //         // Convert merged data back to flattened form for form builder
    //         $formValues = ArrayUtils::flattenWithKeys($mergedData);

    //         $this->session->set($formKey . '_values', $formValues);
    //         $this->session->set($formKey . '_errors', $errors);

    //         return $this->redirect("/admin/product/{$id}/edit");
    //     }

    //     // Update with merged data
    //     if (null !== $imageUpload->getMediaPaths()) {
    //         $mergedData['media'] = $imageUpload->getMediaPaths();
    //     }

    //     $update = $this->product->update([$id], $mergedData);

    //     if ($update->getQueryResult()) {
    //         $this->flash->add('Product updated successfully');
    //         return $this->redirect("/post/{$id}/show");
    //     }

    //     $this->flash->add('Failed to update product', FlashType::DANGER);
    //     return $this->redirect("/admin/product/{$id}/edit");
    // }

    /**
     * Display the product edit form.
     */
    public function editAction(int $id): Response
    {
        $form = $this->form("product/update/{$id}");
        return $this->response('product/edit', ['form' => $form, 'productId' => $id]);
    }

    /**
     * Prepare data for validation by ensuring proper nested structure.
     */
    private function prepareForValidation(array $data): array
    {
        // Filter out system fields
        $filteredData = ArrayUtils::filterSystemFields($data);

        // Ensure variations array has proper structure
        if (isset($filteredData['variations']) && is_array($filteredData['variations'])) {
            $filteredData['variations'] = $this->normalizeVariations($filteredData['variations']);
        }

        return $filteredData;
    }

    /**
     * Normalize variations structure to match validation rules.
     */
    private function normalizeVariations(array $variations): array
    {
        $normalized = [];

        foreach ($variations as $index => $variation) {
            if (is_array($variation)) {
                // Ensure all expected fields are present
                $normalized[$index] = array_merge([
                    'variant_type' => '',
                    'name' => '',
                    'sku' => '',
                    'price_modifier' => '',
                    'stock_quantity' => '',
                    'status' => '',
                    'attributes' => [],
                ], $variation);

                // Normalize attributes if present
                if (isset($variation['attributes']) && is_array($variation['attributes'])) {
                    $normalized[$index]['attributes'] = $this->normalizeAttributes($variation['attributes']);
                }
            }
        }

        return $normalized;
    }

    /**
     * Normalize attributes structure.
     */
    private function normalizeAttributes(array $attributes): array
    {
        $normalized = [];

        foreach ($attributes as $attrIndex => $attribute) {
            if (is_array($attribute)) {
                $normalized[$attrIndex] = array_merge([
                    'attribute_name' => '',
                    'attribute_value' => '',
                ], $attribute);
            }
        }

        return $normalized;
    }
}