<?php

declare(strict_types=1);

class DropzoneFieldHandler implements FieldHandlerInterface
{
    public function supports(string $fieldType): bool
    {
        return $fieldType === 'dropzone';
    }

    public function handle(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        $fieldId = $formInstance->getFieldId($field); // Use centralized ID

        return $form->tag('div')
            ->class(AbstractForm::INPUT_BOX) // Use input-box wrapper for consistency
            ->add(
                $form->tag('h6')
                    ->class('input-box__media-title')
                    ->content($field['title'] ?? ''),
                $form->tag('div')
                    ->class('input-box__media-upload')
                    ->add(...$this->buildDropzoneElements($field, $form, $formInstance, $fieldId)), // Pass the ID
            );
    }

    private function buildDropzoneElements(array $field, FormBuilder $form, AbstractForm $formInstance, string $fieldId): array
    {
        return [
            $this->createMediaPreview($form, $formInstance),
            $form->input('file')
                ->class('media-file')
                ->id($fieldId) // Use the same centralized ID
                ->name($field['name'])
                ->accept($field['accept'] ?? '')
                ->multiple($field['multiple'] ?? false),
            $this->createMediaAvatar($field, $form, $formInstance),
            $form->tag('span')
                ->class('media-text')
                ->content($field['dragText'] ?? ''),
            $form->label()
                ->for($fieldId) // Use the same ID for the label
                ->class('btn', 'btn--secondary', 'btn--md-compact')
                ->add(
                    $form->tag('span')
                        ->class('btn__label')
                        ->content($field['buttonLabel'] ?? 'Add File'),
                ),
        ];
    }

    private function createMediaAvatar(array $field, FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        return $form->tag('div')
            ->class('media-avatar')
            ->add(
                $formInstance->createIcon($form, $field['icon'] ?? '', $field['icon-aria'] ?? ''),
            );
    }

    private function createMediaPreview(FormBuilder $form, AbstractForm $formInstance): AbstractHtmlComponent
    {
        return $form->tag('div')
            ->class('media-preview empty')
            ->add(
                $form->tag('div')
                    ->class('media-preview__item')
                    ->add(
                        $form->tag('div')
                            ->class('media-preview__item--img-container')
                            ->add(
                                $form->tag('img')
                                    ->src('#')
                                    ->alt('Product Image Camera')
                                    ->class('image'),
                            ),
                        $form->tag('div')
                            ->class('media-preview__item--icon-container')
                            ->add(
                                $formInstance->createIcon($form, 'icon-success', 'Success', ['success']),
                            ),
                        $form->button('button')
                            ->class('media-preview__item--remove')
                            ->add(
                                $form->tag('span')
                                    ->class('btn__icon')
                                    ->add(
                                        $formInstance->createIcon($form, 'icon-cancel', 'Cancel', ['cancel']),
                                    ),
                            ),
                    ),
            );
    }
}