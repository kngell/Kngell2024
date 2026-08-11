<?php

declare(strict_types=1);

class StandardFormLayout implements FormLayoutInterface
{
    public function __construct(private array $wrapperClass = [], private array $fieldLayoutClass = [])
    {
    }

    public function buildLayout(
        array $formValues,
        HtmlFormSectionManager $sectionManager,
        SectionRenderer $sectionRenderer,
        HtmlBuilder $form,
        AbstractForm $formInstance,
        formConfig $config,
    ): array {
        $fields = $config->getFields();
        $sectionsConfig = $sectionManager->getSections($formValues);
        $components = [];
        if (empty($sectionsConfig)) {
            $sectionsConfig = $fields;
        }
        foreach ($sectionsConfig as $sectionKey => $section) {
            $result = $sectionRenderer->render(
                $sectionKey,
                $form,
                $sectionsConfig,
                $formInstance,
                $sectionManager,
                $this,
            );

            $components = array_merge($components, is_array($result) ? $result : [$result]);
        }

        return [
            $form->tag('div')->class(...$this->wrapperClass)->add(...$components),
        ];
    }

    public function getFieldSectionLayout(array $fields, string|int $sectionKey, HtmlBuilder $form): AbstractHtmlComponent
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