<?php

declare(strict_types=1);

class ButtonFieldHandler extends AbstractBaseFieldHandler implements FieldHandlerInterface
{
    private const array SUPPORTS = ['button', 'button-group'];

    public function __construct(
        private ButtonBuilder $buttonBuilder,
    ) {
    }

    public function supports(string $fieldType): bool
    {
        return in_array($fieldType, self::SUPPORTS);
    }

    public function handle(array $field, FormBuilder $form, ?AbstractForm $formInstance = null, null|FormConfig|PageConfig $config = null): AbstractHtmlComponent
    {
        return $this->buttonBuilder->build($field);
    }
}