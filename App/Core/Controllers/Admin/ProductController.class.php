<?php

declare(strict_types=1);

class ProductController extends Controller
{
    private const string LAYOUT = 'admin';

    public function __construct(
        private ProductModel $product,
        ProductFormCreator $frm,
        private ValidatorInterface $validator,
        private FileUploadInterface $imgUpload,
    ) {
        $this->layout(self::LAYOUT);
        $this->frm = $frm;
    }

    public function create(): Response
    {
        // This returns expanded nested data, not raw form data
        $expandedData = $this->request->getPost()->getAll();

        // For validation, we need to ensure the nested structure is correct
        $formData = $this->prepareForValidation($expandedData);

        // Validate the properly structured data
        $result = $this->validator->validate($formData, 'productRules');
        $this->imgUpload->proceed(false);

        $errors = array_merge($result->getErrors(), $this->imgUpload->getErrors());

        if (!empty($errors)) {
            $formKey = 'form_' . md5('product/create');

            // For form builder, we need to flatten the data back to form field names
            $formValues = ArrayUtils::flattenWithKeys($formData);

            $this->session->set($formKey . '_values', $formValues);
            $this->session->set($formKey . '_errors', $errors);

            return $this->redirect('/admin/product-add');
        }

        // Process successful submission
        if (null !== $this->imgUpload->getMediaPaths()) {
            $formData['media'] = $this->imgUpload->getMediaPaths();
        }

        $insert = $this->product->save($formData);

        if ($insert->getQueryResult()) {
            $this->flash->add('The product has been created successfully');
            return $this->redirect("/post/{$insert->getLastInsertId()}/show");
        }

        // Handle save failure
        $this->flash->add('Failed to create product', FlashType::DANGER);
        return $this->redirect('/admin/product-add');
    }

    /**
     * Display the product creation form.
     */
    public function createAction(): Response
    {
        $form = $this->form('product/create');
        return $this->response('product/create', ['form' => $form]);
    }

    /**
     * Enhanced method for edit/update with proper form data handling.
     */
    public function update(int $id): Response
    {
        $expandedData = $this->request->getPost()->getAll();
        $formData = $this->prepareForValidation($expandedData);

        // Get existing product data
        $existingProduct = $this->product->first([$id])->asObject();

        if (!$existingProduct) {
            $this->flash->add('Product not found', FlashType::DANGER);
            return $this->redirect('/admin/products');
        }

        // Merge with existing data (useful for partial updates)
        $mergedData = ArrayUtils::mergeFormData($existingProduct->toArray(), $formData);

        $result = $this->validator->validate($mergedData, 'productRules');
        $this->imgUpload->proceed(false);

        $errors = array_merge($result->getErrors(), $this->imgUpload->getErrors());

        if (!empty($errors)) {
            $formKey = 'form_' . md5("product/update/{$id}");

            // Convert merged data back to flattened form for form builder
            $formValues = ArrayUtils::flattenWithKeys($mergedData);

            $this->session->set($formKey . '_values', $formValues);
            $this->session->set($formKey . '_errors', $errors);

            return $this->redirect("/admin/product/{$id}/edit");
        }

        // Update with merged data
        if (null !== $this->imgUpload->getMediaPaths()) {
            $mergedData['media'] = $this->imgUpload->getMediaPaths();
        }

        $update = $this->product->update([$id], $mergedData);

        if ($update->getQueryResult()) {
            $this->flash->add('Product updated successfully');
            return $this->redirect("/post/{$id}/show");
        }

        $this->flash->add('Failed to update product', FlashType::DANGER);
        return $this->redirect("/admin/product/{$id}/edit");
    }

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