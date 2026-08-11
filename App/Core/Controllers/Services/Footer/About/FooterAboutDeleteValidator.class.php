<?php

declare(strict_types=1);

class FooterAboutDeleteValidator extends AbstractDeleteValidator
{
    public function __construct(
        private FooterAboutModel $model,
    ) {
    }

    public function getEntityKeyfield(): ?string
    {
        return $this->model->getEntityKeyfield();
    }

    protected function getLabel(): string
    {
        return DeletionLabel::FOOTER_ABOUT->value;
    }

    protected function findRecord(array $id): ?FooterAbout
    {
        return $this->model->one([$id['key'] => $id['value']])?->asClass();
    }

    /** @param FooterAbout $record */
    protected function resolveDisplayName(Entity $record): ?string
    {
        return null;
    }

    /** @param FooterAbout $record */
    protected function resolveDisplayImage(Entity $record): ?string
    {
        return null;
    }

    /**
     * @param FooterAbout $record
     * @param DeletionValidatorResult $result
     *
     * @return void
     */
    protected function populateMetadata(
        Entity $record,
        DeletionValidatorResult $result,
    ): void {
        $result->setMetadata('content', $record->getContent());
        $result->setMetadata('page_target', '');
    }

    /**
     * @param array $id
     * @param FooterAbout $record
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
                'This section is linked to an active page. '
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