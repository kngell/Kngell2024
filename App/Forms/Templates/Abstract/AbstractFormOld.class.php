<?php

declare(strict_types=1);

abstract readonly class AbstractFormOld implements FormTemplateInterface
{
    private const string INPUT_BOX = 'input-box';
    private const string INPUT_CLASS = 'input-box__input';
    private const string TEXTAREA_CLASS = 'input-box__textarea';
    private const string PREFIX_CLASS = 'input-box__prefix';
    private const string SUFFIX_CLASS = 'input-box__suffix';
    private const string INPUT_CONTAINER = 'input-box__container';
    private const string INPUT_SELECT = 'input-box__select';
    private const string ICON_SPRITE = 'icons-sprite.svg';
    private const string LABEL_CLASS = 'input-box__label';
    private const string HINT_CLASS = 'input-box__hint-text';

    public function __construct(protected HtmlBuilder $builder)
    {
    }

    abstract protected function getFormSections(): array;

    abstract protected function getFormId(): string;

    abstract protected function getFormName(): string;

    abstract protected function getFormClass(): string;

    public function make(string $action = '', array|Entity|bool $formValues = [], array $formErrors = []): string
    {
        $form = $this->builder->form()
            ->formValues($formValues)
            ->formErrors($formErrors);

        return $form->name($this->getFormName())
            ->method('post')
            ->id($this->getFormId())
            ->class($this->getFormClass())
            ->enctype(Enctype::FORM_DATA->value)
            ->add(...$this->buildFormLayout($form))
            ->generate();
    }

    protected function buildFormLayout(FormBuilder $form): array
    {
        return $this->buildFormSections($form);
    }

    protected function buildFormSections(FormBuilder $form): array
    {
        $sections = [];
        $sectionsConfig = $this->getFormSections(); // This calls the child class method

        foreach (array_keys($sectionsConfig) as $sectionKey) {
            $sections[] = $this->renderSection($sectionKey, $form, $sectionsConfig);
        }

        return $sections;
    }

    protected function renderSection(string $sectionKey, FormBuilder $form, array $sectionsConfig): AbstractHtmlComponent
    {
        if (!isset($sectionsConfig[$sectionKey])) {
            throw new InvalidArgumentException("Section '$sectionKey' is not defined.");
        }

        $sectionContent = $sectionsConfig[$sectionKey];

        // Check if this section contains field groups
        if ($this->hasFieldGroups($sectionContent)) {
            $fields = $this->renderSectionWithFieldGroups($sectionContent, $form);
        } else {
            // Regular section with flat fields
            $fields = array_map(
                fn (array $field) => $this->renderField($field, $form),
                $sectionContent,
            );
        }

        $sectionClass = 'frm-section ' . $sectionKey;
        $extraClass = $this->getSectionExtraClass($sectionKey);
        $sectionTitle = $this->getSectionTitle($sectionKey);

        return $form->tag('div')
            ->class($sectionClass)
            ->add(
                $form->tag('h4')
                    ->class('frm-section__title' . $extraClass)
                    ->content($sectionTitle),
                $form->tag('div')
                    ->class('frm-section__body' . $extraClass)
                    ->add(...$fields),
            );
    }

    protected function hasFieldGroups(array $sectionContent): bool
    {
        foreach ($sectionContent as $item) {
            if (isset($item['type']) && $item['type'] === 'field-group') {
                return true;
            }
        }
        return false;
    }

    protected function renderSectionWithFieldGroups(array $sectionContent, FormBuilder $form): array
    {
        $elements = [];

        foreach ($sectionContent as $item) {
            if (isset($item['type']) && $item['type'] === 'field-group') {
                $elements[] = $this->renderFieldGroup($item, $form);
            } else {
                $elements[] = $this->renderField($item, $form);
            }
        }

        return $elements;
    }

    // In AbstractFormBuilder.php

    protected function renderFieldGroup(array $groupConfig, FormBuilder $form): AbstractHtmlComponent
    {
        $wrapperClass = $groupConfig['wrapperClass'] ?? 'field-group';
        $content = $groupConfig['content'] ?? [];

        $groupElements = [];

        // Render mixed content (fields and buttons together)
        foreach ($content as $item) {
            if (isset($item['type']) && $item['type'] === 'button') {
                // Render as button element
                $groupElements[] = $this->renderButton($item, $form);
            } else {
                // Render as regular field with input-box wrapper
                $groupElements[] = $this->renderField($item, $form);
            }
        }

        return $form->tag('div')
            ->class($wrapperClass)
            ->add(...$groupElements);
    }

    // protected function renderFieldGroup(array $groupConfig, FormBuilder $form): AbstractHtmlComponent
    // {
    //     $groupType = $groupConfig['groupType'] ?? 'generic';
    //     $fields = $groupConfig['fields'] ?? [];
    //     $buttons = $groupConfig['buttons'] ?? [];
    //     $wrapperClass = $groupConfig['wrapperClass'] ?? 'field-group';

    //     return $form->tag('div')
    //         ->class($wrapperClass)
    //         ->add(...$this->buildFieldGroupElements($fields, $buttons, $form, $groupType));
    // }

    protected function buildFieldGroupElements(array $fields, array $buttons, FormBuilder $form, string $groupType): array
    {
        $elements = [];

        // Render all fields in the group
        foreach ($fields as $field) {
            $elements[] = $this->renderField($field, $form);
        }

        // Render all buttons in the group
        foreach ($buttons as $button) {
            $elements[] = $this->renderButton($button, $form);
        }

        return $elements;
    }

    // In AbstractFormBuilder.php

    protected function renderButton(array $buttonConfig, FormBuilder $form): AbstractHtmlComponent
    {
        // Create button element
        $button = $form->button()
            ->type($buttonConfig['type'] ?? 'button')
            ->class(...$this->getButtonClasses($buttonConfig));

        // Determine icon position (default: left)
        if (array_key_exists('iconPosition', $buttonConfig)) {
            $iconPosition = $buttonConfig['iconPosition'] ?? 'left';
        } else {
            $iconPosition =  'left';
        }


        // Add button content (icon and/or label)
        $hasIcon = isset($buttonConfig['icon']);
        $hasLabel = isset($buttonConfig['label']);

        if ($hasIcon && $hasLabel) {
            // Both icon and label - respect icon position
            if ($iconPosition === 'right') {
                // Label first, then icon
                $button->add(
                    $form->tag('span')->class('btn__label')->content($buttonConfig['label']),
                );
                $button->add(
                    $form->tag('span')->class('btn__icon')->add(
                        $this->createIcon($form, $buttonConfig['icon'], $buttonConfig['ariaLabel'] ?? 'Button'),
                    ),
                );
            } else {
                // Icon first, then label (default)
                $button->add(
                    $form->tag('span')->class('btn__icon')->add(
                        $this->createIcon($form, $buttonConfig['icon'], $buttonConfig['ariaLabel'] ?? 'Button'),
                    ),
                );
                $button->add(
                    $form->tag('span')->class('btn__label')->content($buttonConfig['label']),
                );
            }
        } elseif ($hasIcon) {
            // Icon only
            $button->add(
                $form->tag('span')->class('btn__icon')->add(
                    $this->createIcon($form, $buttonConfig['icon'], $buttonConfig['ariaLabel'] ?? 'Button'),
                ),
            );
        } elseif ($hasLabel) {
            // Label only
            $button->add(
                $form->tag('span')->class('btn__label')->content($buttonConfig['label']),
            );
        }

        // Add any additional attributes using the custom method
        if (isset($buttonConfig['attributes'])) {
            $button->custom($buttonConfig['attributes']);
        }

        return $button;
    }

    protected function getButtonClasses(array $buttonConfig): array
    {
        $classes = ['btn'];

        // Add size class
        if (isset($buttonConfig['size'])) {
            $classes[] = 'btn--' . $buttonConfig['size'];
        }

        // Add style class
        if (isset($buttonConfig['style'])) {
            $classes[] = 'btn--' . $buttonConfig['style'];
        }

        // Add additional classes
        if (isset($buttonConfig['class'])) {
            $additionalClasses = is_array($buttonConfig['class'])
                ? $buttonConfig['class']
                : explode(' ', $buttonConfig['class']);
            $classes = array_merge($classes, $additionalClasses);
        }

        return $classes;
    }

    protected function getSectionTitle(string $sectionKey): string
    {
        return ucwords(str_replace('-', ' ', $sectionKey));
    }

    protected function getSectionExtraClass(string $sectionKey): string
    {
        $spanAllSections = $this->getSpanAllSections();
        return in_array($sectionKey, $spanAllSections) ? ' span-all' : '';
    }

    protected function getSpanAllSections(): array
    {
        return [];
    }

    protected function renderField(array $field, FormBuilder $form): AbstractHtmlComponent
    {
        $fieldType = $field['type'] ?? 'text';
        $inputElement = $this->createInputElement($field, $form);

        if ($this->hasIconDecorations($field)) {
            $inputElement = $this->wrapWithIcons($field, $inputElement, $form);
        }

        return $this->wrapInInputBox($field, $inputElement, $form);
    }

    protected function createInputElement(array $field, FormBuilder $form): AbstractHtmlComponent
    {
        $fieldId = $this->getFieldId($field);
        $fieldType = $field['type'] ?? 'text';

        return match ($fieldType) {
            'textarea' => $this->createTextarea($field, $fieldId, $form),
            'select' => $this->createSelect($field, $fieldId, $form),
            'dropzone' => $this->createDropzone($field, $form),
            'field-group' => $this->createFieldGroupElement($field, $form),
            'button' => $this->renderButton($field, $form),
            default => $this->createBasicInput($field, $fieldId, $form),
        };
    }

    protected function createFieldGroupElement(array $field, FormBuilder $form): AbstractHtmlComponent
    {
        // For field-group type at the field level (alternative approach)
        return $this->renderFieldGroup($field, $form);
    }

    protected function getFieldId(array $field): string
    {
        if (isset($field['name'])) {
            return 'product-' . $field['name'];
        }
        return '';
    }

    protected function createBasicInput(array $field, string $fieldId, FormBuilder $form): AbstractHtmlComponent
    {
        return $form->input($field['type'] ?? 'text')
            ->name($field['name'])
            ->id($fieldId)
            ->placeholder($field['placeholder'] ?? '')
            ->class(self::INPUT_CLASS);
    }

    protected function createTextarea(array $field, string $fieldId, FormBuilder $form): AbstractHtmlComponent
    {
        return $form->textarea()
            ->name($field['name'])
            ->id($fieldId)
            ->placeholder($field['placeholder'] ?? '')
            ->class(self::TEXTAREA_CLASS);
    }

    protected function createSelect(array $field, string $fieldId, FormBuilder $form): AbstractHtmlComponent
    {
        $select = $form->select()
            ->id($fieldId)
            ->class(self::INPUT_SELECT)
            ->name($field['name']);

        foreach ($field['options'] as $value => $label) {
            $isDisabled = $value === '';
            $select->add(
                $form->option($value, $label)
                    ->disabled($isDisabled)
                    ->selected($isDisabled),
            );
        }

        // Handle icon decorations for select
        if (!empty($field['suffixIcon'])) {
            $container = $form->tag('div')->class(self::INPUT_CONTAINER);
            $container->add($select);
            $container->add(
                $this->createIconWrapper($field['suffixIcon'], self::SUFFIX_CLASS, 'Suffix', $form),
            );

            // Add custom elements if any
            if (isset($field['customElements'])) {
                foreach ($field['customElements'] as $customElement) {
                    $container->add($this->createCustomElement($customElement, $form));
                }
            }

            return $container;
        }

        return $select;
    }

    protected function createDropzone(array $field, FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('div')
            ->class(self::INPUT_BOX)
            ->add(
                $form->tag('h6')
                    ->class('input-box__media-title')
                    ->content($field['label']),
                $form->tag('div')
                    ->class('input-box__media-upload')
                    ->add(...$this->buildDropzoneElements($field, $form)),
            );
    }

    protected function buildDropzoneElements(array $field, FormBuilder $form): array
    {
        return [
            $this->createMediaPreview($form),
            $form->input('file')
                ->class('media-file')
                ->id($this->getFieldId($field))
                ->name($field['name'])
                ->accept($field['accept'] ?? '')
                ->multiple($field['multiple'] ?? false),
            $this->createMediaAvatar($field, $form),
            $form->tag('span')
                ->class('media-text')
                ->content($field['dragText'] ?? ''),
            $form->label()
                ->for($this->getFieldId($field))
                ->class('btn', 'btn--secondary', 'btn--md-compact')
                ->add(
                    $form->tag('span')
                        ->class('btn__label')
                        ->content($field['buttonLabel'] ?? 'Add File'),
                ),
        ];
    }

    protected function createMediaAvatar(array $field, FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('div')
            ->class('media-avatar')
            ->add(
                $this->createIcon($form, $field['icon'] ?? '', "Media {$field['label']} Avatar"),
            );
    }

    protected function createMediaPreview(FormBuilder $form): AbstractHtmlComponent
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
                                $this->createIcon($form, 'icon-success', 'Success', ['success']),
                            ),
                        $form->button('button')
                            ->class('media-preview__item--remove')
                            ->add(
                                $form->tag('span')
                                    ->class('btn__icon')
                                    ->add(
                                        $this->createIcon($form, 'icon-cancel', 'Cancel', ['cancel']),
                                    ),
                            ),
                    ),
            );
    }

    protected function hasIconDecorations(array $field): bool
    {
        return !empty($field['prefixIcon']) || !empty($field['suffixIcon']);
    }

    protected function wrapWithIcons(array $field, AbstractHtmlComponent $inputElement, FormBuilder $form): AbstractHtmlComponent
    {
        $container = $form->tag('div')->class(self::INPUT_CONTAINER);

        if (!empty($field['prefixIcon'])) {
            $container->add($this->createIconWrapper($field['prefixIcon'], self::PREFIX_CLASS, 'Prefix', $form));
        }

        $container->add($inputElement);

        if (!empty($field['suffixIcon'])) {
            $container->add($this->createIconWrapper($field['suffixIcon'], self::SUFFIX_CLASS, 'Suffix', $form));
        }

        return $container;
    }

    protected function createIconWrapper(string $icon, string $wrapperClass, string $ariaLabel, FormBuilder $form): AbstractHtmlComponent
    {
        return $form->tag('span')
            ->class($wrapperClass)
            ->add($this->createIcon($form, $icon, $ariaLabel));
    }

    protected function createIcon(FormBuilder $form, string $icon, string $ariaLabel, array $additionalClasses = []): AbstractHtmlComponent
    {
        $classes = array_merge(['icon'], $additionalClasses);

        return $form->tag('svg')
            ->class(...$classes)
            ->ariaLabel($ariaLabel)
            ->role('img')
            ->add(
                $form->tag('use')->href($this->getMediaIconUrl($icon)),
            );
    }

    protected function wrapInInputBox(array $field, AbstractHtmlComponent $inputElement, FormBuilder $form): AbstractHtmlComponent
    {
        // Handle buttons separately
        if ($field['type'] === 'button') {
            return isset($field['wrapper-class'])
                ? $form->tag('div')->class($field['wrapper-class'])->add($inputElement)
                : $inputElement;
        }

        // Handle custom components and elements
        if (isset($field['customComponent']) || isset($field['customElements'])) {
            $inputElement = $this->handleCustomElements($field, $inputElement, $form);
        }

        // For regular form fields
        $fieldId = $this->getFieldId($field);
        $labelText = $field['label'] ?? ucfirst($field['name']);
        $hintText = $field['hint'] ?? '';

        return $form->tag('div')
            ->class(self::INPUT_BOX . $this->getFieldExtraClass($field))
            ->add(
                $inputElement,
                $form->label($labelText)
                    ->for($fieldId)
                    ->class(self::LABEL_CLASS),
                $form->tag('span')
                    ->class(self::HINT_CLASS)
                    ->content($hintText),
            );
    }

    protected function handleCustomElements(array $field, AbstractHtmlComponent $inputElement, FormBuilder $form): AbstractHtmlComponent
    {
        $customElements = $field['customElements'] ?? [];
        $customComponent = $field['customComponent'] ?? null;

        // If we have a custom component, handle it
        if ($customComponent) {
            $inputElement = $this->injectCustomComponent($field, $inputElement, $form);
        }

        // Then handle regular custom elements
        if (!empty($customElements)) {
            $inputElement = $this->injectCustomElements($field, $inputElement, $form);
        }

        return $inputElement;
    }

    protected function injectCustomComponent(array $field, AbstractHtmlComponent $inputElement, FormBuilder $form): AbstractHtmlComponent
    {
        $componentName = $field['customComponent'];
        $componentMethod = 'create' . ucfirst($componentName) . 'Component';

        // Check if the component method exists in the current class (ProductForm)
        if (method_exists($this, $componentMethod)) {
            $customComponent = $this->$componentMethod($form);

            // If input is already a container, add component to it
            if ($this->isInputContainer($inputElement)) {
                $inputElement->add($customComponent);
                return $inputElement;
            }

            // Otherwise, wrap in container and add both
            $container = $form->tag('div')->class(self::INPUT_CONTAINER);
            $container->add($inputElement);
            $container->add($customComponent);
            return $container;
        }

        return $inputElement;
    }

    protected function injectCustomElements(array $field, AbstractHtmlComponent $inputElement, FormBuilder $form): AbstractHtmlComponent
    {
        $customElements = $field['customElements'] ?? [];

        if ($this->isInputContainer($inputElement)) {
            foreach ($customElements as $customElement) {
                $inputElement->add($this->createCustomElement($customElement, $form));
            }
            return $inputElement;
        }

        $container = $form->tag('div')->class(self::INPUT_CONTAINER);
        $container->add($inputElement);

        foreach ($customElements as $customElement) {
            $container->add($this->createCustomElement($customElement, $form));
        }

        return $container;
    }

    // protected function createCustomComponent(string $componentName, FormBuilder $form): AbstractHtmlComponent
    // {
    //     $methodName = 'create' . ucfirst($componentName) . 'Component';

    //     if (method_exists($this, $methodName)) {
    //         return $this->$methodName($form);
    //     }

    //     // Fallback: return empty div
    //     return $form->tag('div')->class('custom-component-' . $componentName);
    // }

    protected function isInputContainer(AbstractHtmlComponent $element): bool
    {
        // Check if the element already has the input container class
        // You might need to adjust this based on your actual implementation
        return method_exists($element, 'getClass') &&
               str_contains($element->getClass() ?? '', self::INPUT_CONTAINER);
    }

    protected function createCustomElement(array $elementConfig, FormBuilder $form): AbstractHtmlComponent
    {
        $element = $form->tag($elementConfig['tag'] ?? 'div')
            ->class($elementConfig['class'] ?? '');

        // Add content if provided
        if (isset($elementConfig['content'])) {
            $element->content($elementConfig['content']);
        }

        // Add custom attributes
        if (isset($elementConfig['attributes'])) {
            $element->custom($elementConfig['attributes']);
        }

        // Add child elements
        if (isset($elementConfig['children'])) {
            foreach ($elementConfig['children'] as $child) {
                $element->add($this->createCustomElement($child, $form));
            }
        }

        return $element;
    }

    protected function getFieldExtraClass(array $field): string
    {
        if (isset($field['class'])) {
            return is_array($field['class']) ? ' ' . implode(' ', $field['class']) : ' ' . $field['class'];
        }
        return '';
    }

    protected function getMediaIconUrl(string $icon): string
    {
        return '/public/assets/img/' . self::ICON_SPRITE . '#' . $icon;
    }
}
