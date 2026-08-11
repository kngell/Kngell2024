<?php

declare(strict_types=1);

class FooterLinkSaveController extends AbstractBaseSaveController
{
    public function __construct(
        FooterLinkSaveService $saveService,
        SaveWorflowService $saveWorkflow,
        FormCreatorService $frm,
    ) {
        parent::__construct($saveService, $saveWorkflow, $frm);
    }

    protected function getEntitySpecificPageTitle(): string
    {
        return 'Footer Link Section';
    }
}