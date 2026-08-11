<?php

declare(strict_types=1);

interface FormLayoutInterface
{
    public function buildLayout(
        array $formValues,
        HtmlFormSectionManager $sectionManager,
        SectionRenderer $sectionRenderer,
        HtmlBuilder $builder,
        AbstractForm $formInstance,
        FormConfig $config,
    ): array;

    public function getFieldSectionLayout(
        array $fields,
        string|int $sectionKey,
        HtmlBuilder $form,
    ): null|array|AbstractHtmlComponent;
}