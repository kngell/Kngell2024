<?php

declare(strict_types=1);

class FieldGroupFieldHandler extends AbstractBaseFieldHandler implements FieldHandlerInterface
{
    public function __construct(
        private FieldGroupRenderer $fieldGroupRenderer,
    ) {
    }

    public function supports(string $fieldType): bool
    {
        return $fieldType === 'field-group';
    }

    public function handle(array $field, FormBuilder $form, ?AbstractForm $formInstance = null, null|FormConfig|PageConfig $config = null): AbstractHtmlComponent
    {
        return $this->fieldGroupRenderer->renderFieldGroup($field, $form, $formInstance);
    }
}