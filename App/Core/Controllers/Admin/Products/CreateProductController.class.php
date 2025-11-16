<?php

declare(strict_types=1);

class CreateProductController extends Controller
{
    use ProductCrudTraitOperationSupport;
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

    public function add(): Response
    {
        $expandedData = $this->request->getPost()->getAll();
        $hasAnyFile = $this->request->getFiles()->hasAnyFiles();

        if (empty($expandedData) && !$hasAnyFile) {
            $this->flash->add('Cannot Add the Product. There\'s no product data to save');
            return $this->redirect('/admin/product-add');
        }
        $formData = $this->prepareForValidation($expandedData);
        $files = $this->request->getFiles()->all();
        $webPaths = $this->extractWebPathsFromForm($formData);

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
            $cleanedFiled = $imageUpload->cleanupOldTempFiles();

            $this->flash->add('The product has been created successfully');
            return $this->redirect("/admin/{$newProductId}/product-show");
        }

        $imageUpload->cleanupPermanentFiles();
        $imageUpload->cleanup();

        $this->flash->add('Failed to create product due to a database error.', FlashType::DANGER);
        return $this->redirect('/admin/product-add');
    }
}