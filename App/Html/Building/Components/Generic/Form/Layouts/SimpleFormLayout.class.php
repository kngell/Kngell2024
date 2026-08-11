<?php

declare(strict_types=1);

class SimpleFormLayout implements FormLayoutInterface
{
    #[Override]
    public function buildLayout(array $formValues, HtmlFormSectionManager $sectionManager, SectionRenderer $sectionRenderer, HtmlBuilder $form, AbstractForm $formInstance, Formconfig $config): array
    {
        $sectionEnumClass = $config->getEnumClass();
        $sections = $sectionManager->getSections($formValues);
        $components = [];
        foreach ($sectionEnumClass::cases() as $case) {
            $sectionKey = $case->value;
            if (!isset($sections[$sectionKey])) {
                continue;
            }
            $result = $sectionRenderer->render(
                $sectionKey,
                $form,
                $sections,
                $formInstance,
                $sectionManager,
                $this,
                $config,
            );

            $components = array_merge($components, is_array($result) ? $result : [$result]);
        }
        return $components;
    }

    #[Override]
    public function getFieldSectionLayout(array $fields, string|int $sectionKey, HtmlBuilder $form): null|array|AbstractHtmlComponent
    {
        return $fields;
    }
}