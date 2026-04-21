<?php

declare(strict_types=1);

final class DeletionImpactSection extends AbstractBaseHtmlSection
{
    public function getKey(): string
    {
        return ConfirmDeletionSection::IMPACT->value;
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $form = $this->htmlBuilder;
        return $form->tag('section')->class('deletion-impact')->add(
        );
    }
}