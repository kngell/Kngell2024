<?php

declare(strict_types=1);

class FooterSocialsSaveController extends AbstractBaseSaveController
{
    public function __construct(
        FooterSocialSaveService $saveService,
        SaveWorflowService $saveWorkflow,
        FormCreatorService $frm,
    ) {
        parent::__construct($saveService, $saveWorkflow, $frm);
    }

    protected function getEntitySpecificPageTitle(): string
    {
        return 'Footer Socials Section';
    }
}
