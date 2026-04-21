<?php

declare(strict_types=1);

class ProductSaveController extends AbstractBaseSaveController
{
    public function __construct(
        ProductSaveService $saveService,
        FormCreatorService $frm,
        ValidatorInterface $validator,
        FileUploadFactory $uploader,
        FormDataHandlerService $formDataHandler,
    ) {
        parent::__construct(
            $saveService,
            $frm,
            $validator,
            $uploader,
            $formDataHandler,
        );
    }

    protected function getEntitySpecificPageTitle(): string
    {
        return 'Product';
    }
}