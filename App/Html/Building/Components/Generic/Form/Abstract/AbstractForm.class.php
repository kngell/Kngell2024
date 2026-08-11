<?php

declare(strict_types=1);

abstract class AbstractForm extends AbstractHtml implements FormTemplateInterface
{
    use FileTrimTrait;

    protected array|Entity $formValues = [];
    protected array $formErrors = [];
    protected array $files = [];
    protected bool $isEditMode = true;
    protected ?FormConfig $formConfig = null;
    protected ?SectionRenderer $sectionRenderer = null;
    private ?array $mediaFieldsCache = null;

    public function __construct(
        protected null|FormConfig|PageConfig $config = null,
        protected ?HtmlBuilder $builder = null,
        protected ?ButtonBuilder $buttonBuilder = null,
        protected ?IconBuilder $iconBuilder = null,
        protected ?FlashRenderer $flashRenderer = null,
        protected ?FileMetadataService $metadataService = null,
        protected null|HtmlSectionManagerInterface $sectionManager = null,
    ) {
        $this->sectionRenderer = $config->getSectionRenderer();
    }

    public function make(string $action = '', array|Entity $formValues = [], array $formErrors = [], array $files = []): string
    {
        $this->setFormState($formValues, $formErrors, $files);
        $layout = $this->buildLayout($this->builder);
        return $this->builder->form()
             ->formValues($this->formValues)
             ->formErrors($this->formErrors)
             ->name($this->config->getFormName())
             ->novalidate()
             ->method('post')
             ->action($action)
             ->id($this->config->getFormId())
             ->class(...$this->config->getFormClass())
             ->enctype(Enctype::FORM_DATA->value)
             ->custom($this->config->getCustomAttributes())
             ->add(...$layout)
             ->generate();
    }

    public function getSectionTitle(string $sectionKey): string
    {
        return ucwords(str_replace('-', ' ', $sectionKey));
    }

    public function getSectionExtraClass(string $sectionKey): string
    {
        $spanAllSections = $this->getSpanAllSections();
        return in_array($sectionKey, $spanAllSections) ? ' span-all' : '';
    }

    public function hasIconDecorations(array $field): bool
    {
        return !empty($field['prefixIcon']) || !empty($field['suffixIcon']) && $field['type'] !== 'select';
    }

    public function wrapWithIcons(array $field, AbstractHtmlComponent $inputElement, FormBuilder $form): AbstractHtmlComponent
    {
        $container = $form->tag('div')->class(InputBox::INPUT_CONTAINER);

        if (!empty($field['prefixIcon'])) {
            $container->add($this->createIconWrapper($field['prefixIcon'], InputBox::PREFIX_CLASS, 'Prefix', $form));
        }

        $container->add($inputElement);

        if (!empty($field['suffixIcon'])) {
            $container->add($this->createIconWrapper($field['suffixIcon'], InputBox::SUFFIX_CLASS, 'Suffix', $form));
        }

        return $container;
    }

    public function wrapInInputBox(array $field, AbstractHtmlComponent $inputElement, FormBuilder $form): AbstractHtmlComponent
    {
        // Use the cached ID if available, otherwise generate new one
        $fieldId = $this->config->getFieldId($field);

        // Handle buttons separately
        if (in_array($field['type'], ['button', 'dropzone'])) {
            return isset($field['wrapper-class'])
                ? $form->tag('div')->class($field['wrapper-class'])->add($inputElement)
                : $inputElement;
        }

        $labelText = $field['label'] ?? ucfirst($field['name']);
        $hintText = $field['hint'] ?? '';

        return $form->tag('div')
            ->class(InputBox::INPUT_BOX . $this->getFieldExtraClass($field))
            ->add(
                $inputElement,
                $form->label($labelText)
                    ->for($fieldId)
                    ->class(InputBox::LABEL_CLASS),
            );
    }

    public function createIcon(string $icon, string $ariaLabel, array $additionalClasses = []): AbstractHtmlComponent
    {
        return $this->iconBuilder->createIcon($icon, $ariaLabel, $additionalClasses);
    }

    public function createIconWrapper(string $icon, string $wrapperClass, string $ariaLabel, FormBuilder $form): AbstractHtmlComponent
    {
        return $this->iconBuilder->createIconWrapper($icon, $wrapperClass, $ariaLabel);
    }

    public function renderButton(array $buttonConfig, FormBuilder $form): AbstractHtmlComponent
    {
        return $this->buttonBuilder
        ->addConfig($buttonConfig)
        ->build($buttonConfig);
    }

    public function renderButtonGroup(array $buttonConfig, FormBuilder $form): AbstractHtmlComponent
    {
        $content = $buttonConfig['content'];
        $buttonComponents = [];
        foreach ($content as $buttonItem) {
            $buttonComponents[] = $this->buttonBuilder->build($buttonItem);
        }
        return $form->tag('div')->class($buttonConfig['wrapperClass'] ?? '')->add(...$buttonComponents);
    }

    public function renderHtml(array $htmlConfig, FormBuilder $form): AbstractHtmlComponent
    {
        $content = $htmlConfig['content'];
        $tag = $htmlConfig['tag'] ?? 'div';
        return $form->tag($tag)->content($content);
    }

    public function getFiles(array $field): array
    {
        $isMultiple = isset($field['multiple']) && $field['multiple'] === true ? true : false;
        return (new FileExtractor(
            metadataService: $this->metadataService ?? new FileMetadataService(),
            formValues: $this->formValues,
            mediaNames: $this->getMediaFieldNames($isMultiple),
            isEditMode: $this->isEditMode,
        ))->getFiles($field);
    }

    /**
     * @param array $files
     */
    public function setFiles(array $files): void
    {
        $this->files = $files;
    }

    /**
     * @return array
     */
    public function getFormErrors(): array
    {
        return $this->formErrors;
    }

    /**
     * @return array|Entity
     */
    public function getFormValues(): array|Entity
    {
        return $this->formValues;
    }

    abstract public function buildLayout(?HtmlBuilder $form = null): array;

    public function getDefaultInputLayoutName(): ?string
    {
        return null; // Use system default
    }

    /**
     * Forms can override this for per-field exceptions.
     */
    public function getInputLayoutNameForField(array $field): ?string
    {
        return null; // No per-field exceptions
    }

    protected function setInputBoxLayout(string $layoutName): void
    {
        // This would need access to the FieldRenderer
        // We'll handle this differently - see below
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

    protected function getFormConfig(): ?FormConfig
    {
        return $this->formConfig;
    }

    protected function setFormConfig(FormConfig $config): self
    {
        $this->formConfig = $config;
        return $this;
    }

    private function setFormState(null|array|Entity $formValues, array $formErrors, array $files): void
    {
        $this->formValues = $formValues;
        $this->formErrors = $formErrors;
        $this->files = $files;
    }

    private function getMediaFieldNames(bool $isMultiple = false): array
    {
        if ($this->mediaFieldsCache !== null) {
            return $this->mediaFieldsCache;
        }

        $this->mediaFieldsCache = [];
        $sections = $this->sectionManager->getSections();
        foreach ($sections as $sectionFields) {
            foreach ($sectionFields as $field) {
                if ($field instanceof FormFieldConfig) {
                    $field = $field->toArray();
                }
                if (isset($field['type']) && $field['type'] === 'dropzone' && isset($field['name'])) {
                    $this->mediaFieldsCache[] = !$isMultiple ? $this->getBaseFieldName($field['name']) : $field['name'];
                }
            }
        }

        return $this->mediaFieldsCache;
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

            $container = $form->tag('div')->class(InputBox::INPUT_CONTAINER);
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

        $container = $form->tag('div')->class(InputBox::INPUT_CONTAINER);
        $container->add($inputElement);

        foreach ($customElements as $customElement) {
            $container->add($this->createCustomElement($customElement, $form));
        }

        return $container;
    }

    private function isInputContainer(AbstractHtmlComponent $element): bool
    {
        return method_exists($element, 'getClass') &&
               in_array(Inputbox::INPUT_CONTAINER, $element->getClass());
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