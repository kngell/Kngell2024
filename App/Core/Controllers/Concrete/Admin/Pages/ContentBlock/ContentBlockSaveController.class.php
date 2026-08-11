<?php

declare(strict_types=1);

class ContentBlockSaveController extends AbstractBaseSaveController
{
    public function __construct(
        ContentBlockSaveService $saveService,
        SaveWorflowService $saveWorkflow,
        FormCreatorService $frm,
    ) {
        parent::__construct($saveService, $saveWorkflow, $frm);
    }

    protected function getEntitySpecificPageTitle(): string
    {
        $type = $this->session->get('current_block_type');
        $blockType = BlockType::tryFrom($type);
        if (!$blockType) {
            return 'Content Block';
        }
        return $blockType->getPageTitle() ?? 'Content Block';
    }
}