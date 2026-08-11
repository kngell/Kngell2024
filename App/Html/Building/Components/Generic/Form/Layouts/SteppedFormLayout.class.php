<?php

declare(strict_types=1);

class SteppedFormLayout extends AbstractSteppedFormLayout implements FormLayoutInterface
{
    public function buildLayout(
        array $formValues,
        HtmlFormSectionManager $sectionManager,
        SectionRenderer $sectionRenderer,
        HtmlBuilder $builder,
        AbstractForm $formInstance,
        FormConfig $config,
    ): array {
        $activeStepConfig = $this->getActiveStepConfig($config->getStepConfig());

        if (!$activeStepConfig->hasSteps()) {
            return [];
        }
        if ($activeStepConfig->getContentContainerClass()) {
            $this->contentContainerClass = $activeStepConfig->getContentContainerClass();
        }
        $sectionsConfig = $sectionManager->getSections($formValues);
        $fields = $config->getFields();
        $sectionsConfig = array_merge($sectionsConfig, $fields);
        $formSections = $this->buildFormSections(
            $sectionsConfig,
            $sectionRenderer,
            $builder,
            $formInstance,
            $sectionManager,
            $config,
        );

        // Build the stepped layout - returns array of step components directly
        return $this->buildSteppedLayout(
            $activeStepConfig,
            $formSections,
            $builder,
        );
    }

    public function getFieldSectionLayout(
        array $fields,
        string|int $sectionKey,
        HtmlBuilder $form,
    ): ?AbstractHtmlComponent {
        return $form->add(...$fields);
    }

    private function buildFormSections(
        array $sectionsConfig,
        SectionRenderer $sectionRenderer,
        HtmlBuilder $form,
        AbstractForm $formInstance,
        HtmlFormSectionManager $sectionManager,
        FormConfig $config,
    ): array {
        $formSections = [];

        foreach (array_keys($sectionsConfig) as $sectionKey) {
            $result = $sectionRenderer->render(
                $sectionKey,
                $form,
                $sectionsConfig,
                $formInstance,
                $sectionManager,
                $this,
                $config,
            );

            $components = is_array($result) ? $result : [$result];
            $formSections[$sectionKey] = $components;
        }

        return $formSections;
    }
}