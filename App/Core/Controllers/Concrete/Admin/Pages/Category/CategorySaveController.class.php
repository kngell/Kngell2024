<?php

declare(strict_types=1);

class CategorySaveController extends AbstractBaseSaveController
{
    public function __construct(
        CategorySaveService $saveService,
        SaveWorflowService $saveWorkflow,
        FormCreatorService $frm,
    ) {
        parent::__construct($saveService, $saveWorkflow, $frm);
    }

    protected function getEntitySpecificPageTitle(): string
    {
        return 'Category Section';
    }
}