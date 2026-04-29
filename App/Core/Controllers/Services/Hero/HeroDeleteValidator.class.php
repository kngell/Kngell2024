<?php

declare(strict_types=1);

class HeroDeleteValidator extends AbstractDeleteValidator
{
    public function __construct(
        private HeroModel $model,
    ) {
    }

    public function getEntityKeyfield(): ?string
    {
        return $this->model->getEntityKeyfield();
    }

    protected function getLabel(): string
    {
        return 'Hero Section';
    }

    protected function findRecord(array $id): ?object
    {
        return $this->model->one([$id['key'] => $id['value']])?->asClass();
    }

    /** @param Hero $record */
    protected function resolveDisplayName(Entity $record): string
    {
        return $record->getTitle();
    }

    /** @param Hero $record */
    protected function resolveDisplayImage(Entity $record): ?string
    {
        return $record->getImageUrl();
    }

    /** @param Hero $record */
    protected function populateMetadata(
        Entity $record,
        DeletionValidatorResult $result,
    ): void {
        $result->setMetadata('title', $record->getTitle());
        $result->setMetadata('page_target', $record->getPageTarget());
    }

    /** @param Hero $record */
    protected function checkBusinessRules(
        array $id,
        Entity $record,
        DeletionValidatorResult $result,
    ): void {
        if ($this->isLinkedToActivePage($id)) {
            $result->addWarning(
                'This hero section is linked to an active page. '
                . 'The page will display a fallback section.',
            );
        }

        if ($record->getIsActive()) {
            $result->addWarning(
                'This hero section is currently active and visible.',
            );
        }
    }

    private function isLinkedToActivePage(array $id): bool
    {
        // TODO: Implement
        return false;
    }
}