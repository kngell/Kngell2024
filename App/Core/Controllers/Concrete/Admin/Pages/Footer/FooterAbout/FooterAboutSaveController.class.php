<?php

declare(strict_types=1);

class FooterAboutSaveController extends AbstractBaseSaveController
{
    public function __construct(
        FooterAboutSaveService $saveService,
        SaveWorflowService $saveWorkflow,
        FormCreatorService $frm,
    ) {
        parent::__construct($saveService, $saveWorkflow, $frm);
    }

    protected function getEntitySpecificPageTitle(): string
    {
        return 'Footer About Section';
    }
}