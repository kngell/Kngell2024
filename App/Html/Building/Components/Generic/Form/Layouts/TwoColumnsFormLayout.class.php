<?php

declare(strict_types=1);

class TwoColumnsFormLayout implements FormLayoutInterface
{
    public function __construct(
        private array $leftSections = [],
        private array $leftColumnClass = [],
        private array $rightColumnClass = [],
    ) {
        $this->leftSections = $leftSections;
    }

    public function buildLayout(
        array $formValues,
        HtmlFormSectionManager $sectionManager,
        SectionRenderer $sectionRenderer,
        HtmlBuilder $form,
        AbstractForm $formInstance,
        FormConfig $config,
    ): array {
        $sectionsConfig = $sectionManager->getSections($formValues);
        $leftComponents = [];
        $rightComponents = [];

        foreach ($sectionsConfig as $key => $section) {
            $result = $sectionRenderer->render(
                $key,
                $form,
                $sectionsConfig,
                $formInstance,
                $sectionManager,
                $this,
                $config,
            );

            $component = is_array($result) ? $result : [$result];

            // Add to appropriate column
            if (in_array($key, $this->leftSections)) {
                $leftComponents = array_merge($leftComponents, $component);
            } else {
                $rightComponents = array_merge($rightComponents, $component);
            }
        }

        return [
            $form->tag('div')->class(...$this->leftColumnClass)->add(...$leftComponents),
            $form->tag('div')->class(...$this->rightColumnClass)->add(...$rightComponents),
        ];
    }

    public function getFieldSectionLayout(
        array $fields,
        string|int $sectionKey,
        HtmlBuilder $form,
    ): ?AbstractHtmlComponent {
        return $form->div()->class('form-row')->add(...$fields);
    }
}