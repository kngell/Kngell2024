<?php

declare(strict_types=1);

abstract readonly class AbstractForm implements FormTemplateInterface
{
    public const string INPUT_BOX = 'input-box';
    public const string INPUT_CLASS = 'input-box__input';
    public const string TEXTAREA_CLASS = 'input-box__textarea';
    public const string PREFIX_CLASS = 'input-box__prefix';
    public const string PREFIX_CURRENCY_CLASS = 'input-box__prefix--currency';

    public const string SUFFIX_CLASS = 'input-box__suffix';
    public const string INPUT_CONTAINER = 'input-box__container';
    public const string INPUT_CONTAINER_CURRENCY = 'input-box__container--currency';
    public const string INPUT_SELECT = 'input-box__select';
    public const string ICON_SPRITE = 'icons-sprite.svg';
    public const string LABEL_CLASS = 'input-box__label';
    public const string HINT_CLASS = 'input-box__hint-text';

    public function __construct(
        protected HtmlBuilder $builder,
        private FieldRenderer $fieldRenderer,
        private FieldGroupRenderer $FieldGroupRenderer,
        protected SectionRenderer $sectionRenderer,
        private ButtonBuilder $buttonBuilder,
        private IconBuilder $iconBuilder,
        private FieldIdGenerator $idGenerator,
    ) {
        $this->FieldGroupRenderer->setFieldRenderer($this->fieldRenderer);
        $this->sectionRenderer
        ->fieldRenderer($this->fieldRenderer)
        ->fieldGroupRenderer($this->FieldGroupRenderer);
    }

    public function make(string $action = '', array|Entity|bool $formValues = [], array $formErrors = []): string
    {
        $this->idGenerator->reset();
        $form = $this->builder->form()
            ->formValues($formValues)
            ->formErrors($formErrors);

        return $form->name($this->getFormName())
            ->method('post')
            ->action($action)
            ->id($this->getFormId())
            ->class($this->getFormClass())
            ->enctype(Enctype::FORM_DATA->value)
            ->custom($this->getFormCustomAttributes())
            ->add(...$this->buildFormLayout($form))
            ->generate();
    }

    // Essential methods used by child classes and renderers
    public function getSectionTitle(string $sectionKey): string
    {
        return ucwords(str_replace('-', ' ', $sectionKey));
    }

    public function getSectionExtraClass(string $sectionKey): string
    {
        $spanAllSections = $this->getSpanAllSections();
        return in_array($sectionKey, $spanAllSections) ? ' span-all' : '';
    }

    public function getFieldId(array $field): string
    {
        return $this->idGenerator->generateId($this->getFormId(), $field);
    }

    // Reset for new form generation
    public function resetFieldIds(): void
    {
        $this->idGenerator->reset();
    }

    // Methods used by field handlers and builders
    public function hasIconDecorations(array $field): bool
    {
        return !empty($field['prefixIcon']) || !empty($field['suffixIcon']) && $field['type'] !== 'select';
    }

    public function wrapWithIcons(array $field, AbstractHtmlComponent $inputElement, FormBuilder $form): AbstractHtmlComponent
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

    // In AbstractForm.php - temporary debug method
    public function wrapInInputBox(array $field, AbstractHtmlComponent $inputElement, FormBuilder $form): AbstractHtmlComponent
    {
        // Use the cached ID if available, otherwise generate new one
        $fieldId = $this->getFieldId($field);

        // Handle buttons separately
        if (in_array($field['type'], ['button', 'dropzone'])) {
            return isset($field['wrapper-class'])
                ? $form->tag('div')->class($field['wrapper-class'])->add($inputElement)
                : $inputElement;
        }

        if ($field['type'] === 'input-with-currency') {
        }

        // For regular form fields
        $labelText = $field['label'] ?? ucfirst($field['name']);
        $hintText = $field['hint'] ?? '';

        return $form->tag('div')
            ->class(self::INPUT_BOX . $this->getFieldExtraClass($field))
            ->add(
                $inputElement,
                $form->label($labelText)
                    ->for($fieldId) // Ensure this matches the input element's ID
                    ->class(self::LABEL_CLASS),
            );
    }

    // Delegate to specialized builders
    public function createIcon(FormBuilder $form, string $icon, string $ariaLabel, array $additionalClasses = []): AbstractHtmlComponent
    {
        return $this->iconBuilder->createIcon($form, $icon, $ariaLabel, $additionalClasses);
    }

    public function createIconWrapper(string $icon, string $wrapperClass, string $ariaLabel, FormBuilder $form): AbstractHtmlComponent
    {
        return $this->iconBuilder->createIconWrapper($icon, $wrapperClass, $ariaLabel, $form);
    }

    public function renderButton(array $buttonConfig, FormBuilder $form): AbstractHtmlComponent
    {
        return $this->buttonBuilder->build($buttonConfig, $form, $this);
    }

    public function renderButtonGroup(array $buttonConfig, FormBuilder $form): AbstractHtmlComponent
    {
        $content = $buttonConfig['content'];
        $buttonComponents = [];
        foreach ($content as $buttonItem) {
            $buttonComponents[] = $this->buttonBuilder->build($buttonItem, $form, $this);
        }
        return $form->tag('div')->class($buttonConfig['wrapperClass'] ?? '')->add(...$buttonComponents);
    }

    public function renderHtml(array $htmlConfig, FormBuilder $form): AbstractHtmlComponent
    {
        $content = $htmlConfig['content'];
        $tag = $htmlConfig['tag'] ?? 'div';
        return $form->tag($tag)->content($content);
    }

    abstract protected function getFormSections(): array;

    abstract protected function getFormId(): string;

    abstract protected function getFormName(): string;

    abstract protected function getFormClass(): string;

    abstract protected function buildFormLayout(FormBuilder $form): array;

    protected function getFormCustomAttributes(): array
    {
        return [];
    }

    protected function buildFormSections(FormBuilder $form): array
    {
        $sections = [];
        $sectionsConfig = $this->getFormSections();

        foreach (array_keys($sectionsConfig) as $sectionKey) {
            $sections[] = $this->sectionRenderer->render($sectionKey, $form, $sectionsConfig, $this);
        }

        return $sections;
    }

    protected function getSpanAllSections(): array
    {
        return [];
    }

    protected function getFieldExtraClass(array $field): string
    {
        if (isset($field['class'])) {
            return is_array($field['class']) ? ' ' . implode(' ', $field['class']) : ' ' . $field['class'];
        }
        return '';
    }

    // Custom elements handling (keep this in AbstractForm as it's form-specific)
    protected function handleCustomElements(array $field, AbstractHtmlComponent $inputElement, FormBuilder $form): AbstractHtmlComponent
    {
        $customElements = $field['customElements'] ?? [];
        $customComponent = $field['customComponent'] ?? null;

        if ($customComponent) {
            $inputElement = $this->injectCustomComponent($field, $inputElement, $form);
        }

        if (!empty($customElements)) {
            $inputElement = $this->injectCustomElements($field, $inputElement, $form);
        }

        return $inputElement;
    }

    private function injectCustomComponent(array $field, AbstractHtmlComponent $inputElement, FormBuilder $form): AbstractHtmlComponent
    {
        $componentName = $field['customComponent'];
        $componentMethod = 'create' . ucfirst($componentName) . 'Component';

        if (method_exists($this, $componentMethod)) {
            $customComponent = $this->$componentMethod($form);

            if ($this->isInputContainer($inputElement)) {
                $inputElement->add($customComponent);
                return $inputElement;
            }

            $container = $form->tag('div')->class(self::INPUT_CONTAINER);
            $container->add($inputElement);
            $container->add($customComponent);
            return $container;
        }

        return $inputElement;
    }

    private function injectCustomElements(array $field, AbstractHtmlComponent $inputElement, FormBuilder $form): AbstractHtmlComponent
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

    private function isInputContainer(AbstractHtmlComponent $element): bool
    {
        return method_exists($element, 'getClass') &&
               str_contains($element->getClass() ?? '', self::INPUT_CONTAINER);
    }

    private function createCustomElement(array $elementConfig, FormBuilder $form): AbstractHtmlComponent
    {
        $element = $form->tag($elementConfig['tag'] ?? 'div')
            ->class($elementConfig['class'] ?? '');

        if (isset($elementConfig['content'])) {
            $element->content($elementConfig['content']);
        }

        if (isset($elementConfig['attributes'])) {
            $element->custom($elementConfig['attributes']);
        }

        if (isset($elementConfig['children'])) {
            foreach ($elementConfig['children'] as $child) {
                $element->add($this->createCustomElement($child, $form));
            }
        }

        return $element;
    }
}