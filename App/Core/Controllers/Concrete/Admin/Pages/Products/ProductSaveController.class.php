<?php

declare(strict_types=1);

final class ProductSaveController extends AbstractBaseSaveController
{
    public function __construct(
        ProductSaveService $saveService,
        SaveWorflowService $saveWorkflow,
        FormCreatorService $frm,
    ) {
        parent::__construct($saveService, $saveWorkflow, $frm);
    }

    protected function getEntitySpecificPageTitle(): string
    {
        return 'Save Product';
    }
}