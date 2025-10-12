<?php

declare(strict_types=1);

class HtmlHandler implements FieldHandlerInterface
{
    public function supports(string $fieldType): bool
    {
        return in_array($fieldType, ['html']);
    }

    public function handle(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $fieldId = $formInstance->getFieldId($field);
        $tag = $field['tag'] ?? 'div';
        $container = $form->tag($tag);

        if ($fieldId) {
            $container->id($fieldId);
        }
        if (array_key_exists('class', $field)) {
            $container->class($field['class']);
        }
        if (array_key_exists('wrapperClass', $field)) {
            $container->class($field['wrapperClass']);
        }
        return $container->content($field['content']);
    }
}