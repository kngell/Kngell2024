<?php

declare(strict_types=1);

class CategorySaveController extends AbstractBaseSaveController
{
    public function __construct(
        CategorySaveService $saveService,
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
        return 'Category Section';
    }
}