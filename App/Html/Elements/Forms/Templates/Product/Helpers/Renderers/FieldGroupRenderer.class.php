<?php

declare(strict_types=1);

class FieldGroupRenderer
{
    private ?FieldRenderer $fieldRenderer = null;

    public function setFieldRenderer(FieldRenderer $fieldRenderer): void
    {
        $this->fieldRenderer = $fieldRenderer;
    }

    public function renderFieldGroup(array $groupConfig, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $wrapperClass = $groupConfig['wrapperClass'] ?? 'field-group';
        $content = $groupConfig['content'] ?? [];

        $groupElements = [];

        foreach ($content as $item) {
            if (isset($item['type'])) {
                switch ($item['type']) {
                    case 'field-group':
                        $groupElements[] = $this->renderFieldGroup($item, $form, $formInstance);
                        break;
                    case 'button':
                        $groupElements[] = $formInstance->renderButton($item, $form);
                        break;
                    case 'button-group':
                        $groupElements[] = $formInstance->renderButtonGroup($item, $form);
                        break;
                    case 'html':
                        $groupElements[] = $formInstance->renderHtml($item, $form);
                        break;
                    default:
                        $groupElements[] = $this->fieldRenderer->render($item, $form, $formInstance);
                }
            } else {
                $groupElements[] = $this->fieldRenderer->render($item, $form, $formInstance);
            }
        }

        return $form->tag('div')
            ->class($wrapperClass)
            ->add(...$groupElements);
    }
}