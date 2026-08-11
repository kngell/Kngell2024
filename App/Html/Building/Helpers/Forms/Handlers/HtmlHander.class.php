<?php

declare(strict_types=1);

class HtmlHandler extends AbstractBaseFieldHandler implements FieldHandlerInterface
{
    public function supports(string $fieldType): bool
    {
        return in_array($fieldType, ['html']);
    }

    public function handle(array $field, FormBuilder $form, ?AbstractForm $formInstance = null, null|FormConfig|PageConfig $config = null): AbstractHtmlComponent
    {
        $fieldId = $config->getFieldId($field);
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