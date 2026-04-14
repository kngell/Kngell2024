<?php

declare(strict_types=1);

class HeroSectionSaveController extends AbstractBaseSaveController
{
    public function __construct(
        HeroSectionSaveService $saveService,
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
        return 'Hero Section';
    }
}