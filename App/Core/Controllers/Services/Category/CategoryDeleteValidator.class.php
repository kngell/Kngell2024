<?php

declare(strict_types=1);

class CategoryDeleteValidator extends AbstractDeleteValidator
{
    public function __construct(
        private CategoryModel $model,
    ) {
    }

    public function getEntityKeyfield(): ?string
    {
        return $this->model->getEntityKeyfield();
    }

    protected function getLabel(): string
    {
        return DeletionLabel::CATEGORY->value;
    }

    protected function findRecord(array $id): ?Category
    {
        return $this->model->one([$id['key'] => $id['value']])?->asClass();
    }

    /** @param Category $record */
    protected function resolveDisplayName(Entity $record): string
    {
        return $record->getName();
    }

    /** @param Category $record */
    protected function resolveDisplayImage(Entity $record): ?string
    {
        return $record->getImageUrl();
    }

    /**
     * @param Category $record
     * @param DeletionValidatorResult $result
     *
     * @return void
     */
    protected function populateMetadata(
        Entity $record,
        DeletionValidatorResult $result,
    ): void {
        $result->setMetadata('title', $record->getName());
        $result->setMetadata('page_target', '');
    }

    /**
     * @param array $id
     * @param Category $record
     * @param DeletionValidatorResult $result
     *
     * @return void
     */
    protected function checkBusinessRules(
        array $id,
        Entity $record,
        DeletionValidatorResult $result,
    ): void {
        if ($this->isLinkedToActivePage($id)) {
            $result->addWarning(
                'This Category section is linked to an active page. '
                . 'The page will display a fallback section.',
            );
        }

        if ($record->getIsActive()) {
            $result->addWarning(
                'This Category section is currently active and visible.',
            );
        }
    }

    private function isLinkedToActivePage(array $id): bool
    {
        // TODO: Implement
        return false;
    }
}