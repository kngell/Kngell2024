<?php

declare(strict_types=1);

class FooterColumnSaveController extends AbstractBaseSaveController
{
    public function __construct(
        FooterColumnSaveService $saveService,
        SaveWorflowService $saveWorkflow,
        FormCreatorService $frm,
    ) {
        parent::__construct($saveService, $saveWorkflow, $frm);
    }

    protected function getEntitySpecificPageTitle(): string
    {
        return 'Footer Menu Section';
    }
}