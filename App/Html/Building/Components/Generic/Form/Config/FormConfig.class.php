<?php

declare(strict_types=1);

class FormConfig extends RendererConfig implements SectionConfigInterface
{
    private string $formKey;
    private ?string $headerTitle = null;
    private mixed $beforeRenderCallback = null;
    private array $numericFields = [];
    private ?string $defaultSectionTitle = null;
    private ?string $defaultSectionIcon = null;
    private ?string $formName = null;

    // Field handlers

    // Form behavior
    private ?string $formHandlerClass = null;
    private ?string $validatorClass = null;
    private string $submitText = 'Save';
    private string $submitIcon = 'icon-save';
    private array $customAttributes = [];
    private array $formClass = [];
    private bool $footerEnabled = true;
    private ?string $formSectionEnumClass = null;

    private function __construct(string $formKey)
    {
        $this->formKey = $formKey;
        parent::__construct();
    }

    // Basic getters/setters
    public function getFormKey(): string
    {
        return $this->formKey;
    }

    public function setFormKey(string $formKey): static
    {
        $this->formKey = $formKey;
        return $this;
    }

    public function getHeaderTitle(): ?string
    {
        return $this->headerTitle;
    }

    public function setHeaderTitle(?string $headerTitle): static
    {
        $this->headerTitle = $headerTitle;
        return $this;
    }

    public function getBeforeRenderCallback(): mixed
    {
        return $this->beforeRenderCallback;
    }

    public function setBeforeRenderCallback(mixed $beforeRenderCallback): static
    {
        $this->beforeRenderCallback = $beforeRenderCallback;
        return $this;
    }

    public function getDefaultSectionTitle(): ?string
    {
        return $this->defaultSectionTitle;
    }

    public function setDefaultSectionTitle(?string $defaultSectionTitle): static
    {
        $this->defaultSectionTitle = $defaultSectionTitle;
        return $this;
    }

    public function getDefaultSectionIcon(): ?string
    {
        return $this->defaultSectionIcon;
    }

    public function setDefaultSectionIcon(?string $defaultSectionIcon): static
    {
        $this->defaultSectionIcon = $defaultSectionIcon;
        return $this;
    }

    public function getFormName(): ?string
    {
        return $this->formName;
    }

    public function setFormName(?string $formName): static
    {
        $this->formName = $formName;
        return $this;
    }

    public function getFormHandlerClass(): ?string
    {
        return $this->formHandlerClass;
    }

    public function setFormHandlerClass(?string $formHandlerClass): static
    {
        $this->formHandlerClass = $formHandlerClass;
        return $this;
    }

    public function getValidatorClass(): ?string
    {
        return $this->validatorClass;
    }

    public function setValidatorClass(?string $validatorClass): static
    {
        $this->validatorClass = $validatorClass;
        return $this;
    }

    public function getSubmitText(): string
    {
        return $this->submitText;
    }

    public function setSubmitText(string $submitText): static
    {
        $this->submitText = $submitText;
        return $this;
    }

    public function getSubmitIcon(): string
    {
        return $this->submitIcon;
    }

    public function setSubmitIcon(string $submitIcon): static
    {
        $this->submitIcon = $submitIcon;
        return $this;
    }

    public function getCustomAttributes(): array
    {
        return $this->customAttributes;
    }

    public function setCustomAttributes(array $customAttributes): static
    {
        $this->customAttributes = $customAttributes;
        return $this;
    }

    public function getFormClass(): array
    {
        return $this->formClass;
    }

    public function setFormClass(array $formClass): static
    {
        $this->formClass = $formClass;
        return $this;
    }

    public function isFooterEnabled(): bool
    {
        return $this->footerEnabled;
    }

    public function setFooterEnabled(bool $enabled): static
    {
        $this->footerEnabled = $enabled;
        return $this;
    }

    public function setNumericFields(array $fields): static
    {
        $this->numericFields = $fields;
        return $this;
    }

    public function getNumericFields(): array
    {
        return $this->numericFields;
    }

    /**
     * @return null|string
     */
    public function getFormSectionEnumClass(): ?string
    {
        return $this->formSectionEnumClass;
    }

    /**
     * @param null|string $formSectionEnumClass
     *
     * @return FormConfig
     */
    public function setFormSectionEnumClass(?string $formSectionEnumClass): FormConfig
    {
        $this->formSectionEnumClass = $formSectionEnumClass;

        return $this;
    }

    public static function create(string $formKey): static
    {
        return new self($formKey);
    }
}