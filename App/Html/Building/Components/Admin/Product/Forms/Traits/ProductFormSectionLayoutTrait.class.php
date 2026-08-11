<?php

declare(strict_types=1);

trait ProductFormSectionLayoutTrait
{
    #[Override]
    public function getSectionLayout(array $fields, string $sectionKey, HtmlBuilder $form): null|array|AbstractHtmlComponent
    {
        $sectionClass = 'frm-section ' . $sectionKey;
        $sectionTitle = $this->getSectionTitle($sectionKey);

        return $form->tag('div')
            ->class($sectionClass)
            ->add(
                $form->tag('h4')
                    ->class('frm-section__title')
                    ->content($sectionTitle),
                $form->tag('div')
                    ->class('frm-section__body')
                    ->add(...$fields),
            );
    }

    private function getSectionTitle(string $sectionKey): string
    {
        return ucwords(str_replace('-', ' ', $sectionKey));
    }
}