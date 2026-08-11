<?php

declare(strict_types=1);

class FooterColumnDeleteValidator extends AbstractDeleteValidator
{
    public function __construct(
        private FooterMenuColumnModel $model,
    ) {
    }

    public function getEntityKeyfield(): ?string
    {
        return $this->model->getEntityKeyfield();
    }

    protected function getLabel(): string
    {
        return DeletionLabel::FOOTER_MENU_COLUMN->value;
    }

    protected function findRecord(array $id): ?FooterMenuColumn
    {
        return $this->model->one([$id['key'] => $id['value']])?->asClass();
    }

    /** @param FooterMenuColumn $record */
    protected function resolveDisplayName(Entity $record): string
    {
        return $record->getTitle();
    }

    /** @param FooterMenuColumn $record */
    protected function resolveDisplayImage(Entity $record): ?string
    {
        return null;
    }

    /**
     * @param FooterMenuColumn $record
     * @param DeletionValidatorResult $result
     *
     * @return void
     */
    protected function populateMetadata(
        Entity $record,
        DeletionValidatorResult $result,
    ): void {
        $result->setMetadata('title', $record->getTitle());
        $result->setMetadata('page_target', '');
    }

    /**
     * @param array $id
     * @param FooterMenuColumn $record
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
                'This  section is linked to an active page. '
                . 'The page will display a fallback section.',
            );
        }

        if ($record->getIsActive()) {
            $result->addWarning(
                'This section is currently active and visible.',
            );
        }
    }

    private function isLinkedToActivePage(array $id): bool
    {
        // TODO: Implement
        return false;
    }
}