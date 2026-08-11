<?php

declare(strict_types=1);

class ContentBlockDeleteController extends AbstractDeleteController
{
    public function __construct(
        private ContentBlockDeleteService $deleteService,
    ) {
        if (method_exists($this->deleteService, 'setBlocktype')) {
            $this->deleteService->setBlocktype($this->blockType);
        }
    }

    protected function getDeleteService(): AbstractDeleteService
    {
        return $this->deleteService;
    }

    protected function getLabel(): string
    {
        if ($this->blockType === null) {
            throw new InvalidArgumentException('Invalid block type');
        }
        return DeletionLabel::CONTENT_BLOCK->getLabel($this->blockType);
    }

    protected function resolveRedirectUrl(): string
    {
        if ($this->blockType === null) {
            throw new InvalidArgumentException('Invalid block type');
        }
        return ContentBlockLinks::getListRoute($this->blockType);
    }
}