<?php

declare(strict_types=1);

class ContentBlockDeleteValidator extends AbstractDeleteValidator
{
    public function __construct(
        private ContentBlockModel $model,
    ) {
    }

    public function getEntityKeyfield(): ?string
    {
        return $this->model->getEntityKeyfield();
    }

    protected function getLabel(): string
    {
        return DeletionLabel::CONTENT_BLOCK->value;
    }

    protected function findRecord(array $id): ?object
    {
        return $this->model->one([$id['key'] => $id['value']])?->asClass();
    }

    /** @param ContentBlock $record */
    protected function resolveDisplayName(Entity $record): string
    {
        return $record->getTitle();
    }

    /** @param ContentBlock $record */
    protected function resolveDisplayImage(Entity $record): null|string|array
    {
        $blockMetadata = $record->getBlockMetadata();
        $blockType = $record->getBlockType();
        return match ($blockType) {
            BlockType::HERO,BlockType::SMALL_BANNER,BlockType::BIG_BANNER => $blockMetadata['image']['url'] ?? null,
        };
    }

    /** @param ContentBlock $record */
    protected function populateMetadata(
        Entity $record,
        DeletionValidatorResult $result,
    ): void {
        $result->setMetadata('title', $record->getTitle());
        $result->setMetadata('page_target', $record->getPageTarget());
    }

    /** @param ContentBlock $record */
    protected function checkBusinessRules(
        array $id,
        Entity $record,
        DeletionValidatorResult $result,
    ): void {
        if ($this->isLinkedToActivePage($id)) {
            $result->addWarning(
                'This content block is linked to an active page. '
                . 'The page will display a fallback section.',
            );
        }

        if ($record->getIsActive()) {
            $result->addWarning(
                'This content block is currently active and visible.',
            );
        }
    }

    private function isLinkedToActivePage(array $id): bool
    {
        // TODO: Implement
        return false;
    }
}