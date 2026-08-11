<?php

declare(strict_types=1);
final class FormFieldConfig
{
    private const array DO_NOT_NEED_BODY = ['toggle-switch'];

    public function __construct(
        private string $name,
        private string $type,
        private ?string $label = null,
        private ?string $map = null,
        private mixed $defaultValue = null,
        private bool $required = false,
        private ?string $placeholder = null,
        private ?string $hint = null,
        private array $options = [],
        private array $attributes = [],
        private array $wrapperClass = [],
        private ?string $inputLayout = null,
        private ?array $rightIcon = null,
        private bool $searchable = false,
        private ?array $footer = null,
        private ?string $dropzoneKey = null,  // References a DropzoneConfig
        private ?string $searchPlaceholder = null,
        private ?bool $hasValue = null,
        private ?int $rows = null,
        private ?int $maxlength = null,
        private ?string $step = null,
        private ?bool $multiple = null,
        private ?bool $disabled = null,
        private ?bool $readonly = null,
        private ?bool $needBody = null,
        private ?string $position = 'right',
        private ?string $id = null,
        private array $class = [],
    ) {
        if (in_array($type, self::DO_NOT_NEED_BODY)) {
            $this->needBody = false;
        }
    }

    public function toArray(): array
    {
        $array = [
            'name' => $this->name,
            'type' => $this->type,
            'required' => $this->required,
            'id' => $this->id,
        ];

        if ($this->label !== null) {
            $array['label'] = $this->label;
        }
        if ($this->map !== null) {
            $array['map'] = $this->map;
        }
        if ($this->defaultValue !== null) {
            $array['default'] = $this->defaultValue;
        }
        if ($this->placeholder !== null) {
            $array['placeholder'] = $this->placeholder;
        }
        if ($this->hint !== null) {
            $array['hint'] = $this->hint;
        }
        if (!empty($this->options)) {
            $array['options'] = $this->options;
        }
        if (!empty($this->wrapperClass)) {
            $array['wrapperClass'] = $this->wrapperClass;
        }
        if ($this->inputLayout !== null) {
            $array['inputLayout'] = $this->inputLayout;
        }
        if ($this->rightIcon !== null) {
            $array['rightIcon'] = $this->rightIcon;
        }
        if ($this->searchable) {
            $array['searchable'] = true;
        }
        if ($this->footer !== null) {
            $array['footer'] = $this->footer;
        }
        if ($this->dropzoneKey !== null) {
            $array['dropzoneKey'] = $this->dropzoneKey;
        }
        if ($this->searchPlaceholder !== null) {
            $array['searchPlaceholder'] = $this->searchPlaceholder;
        }
        if ($this->hasValue !== null) {
            $array['has-value'] = $this->hasValue;
        }
        if ($this->rows !== null) {
            $array['rows'] = $this->rows;
        }
        if ($this->maxlength !== null) {
            $array['maxlength'] = $this->maxlength;
        }
        if ($this->step !== null) {
            $array['step'] = $this->step;
        }
        if ($this->multiple !== null) {
            $array['multiple'] = $this->multiple;
        }
        if ($this->disabled !== null) {
            $array['disabled'] = $this->disabled;
        }
        if ($this->readonly !== null) {
            $array['readonly'] = $this->readonly;
        }
        if ($this->needBody !== null) {
            $array['need-body'] = $this->needBody;
        }
        if ($this->position !== null) {
            $array['labelPosition'] = $this->position;
        }

        if (!empty($this->attributes)) {
            $array = array_merge($array, $this->attributes);
        }

        return $array;
    }

    // Getters
    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getMap(): ?string
    {
        return $this->map;
    }

    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getHint(): ?string
    {
        return $this->hint;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getWrapperClass(): array
    {
        return $this->wrapperClass;
    }

    public function getInputLayout(): ?string
    {
        return $this->inputLayout;
    }

    public function getRightIcon(): ?array
    {
        return $this->rightIcon;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function getFooter(): ?array
    {
        return $this->footer;
    }

    public function getDropzoneKey(): ?string
    {
        return $this->dropzoneKey;
    }

    public function getSearchPlaceholder(): ?string
    {
        return $this->searchPlaceholder;
    }

    public function getHasValue(): ?bool
    {
        return $this->hasValue;
    }

    public function getRows(): ?int
    {
        return $this->rows;
    }

    public function getMaxlength(): ?int
    {
        return $this->maxlength;
    }

    public function getStep(): ?string
    {
        return $this->step;
    }

    public function isMultiple(): ?bool
    {
        return $this->multiple;
    }

    public function isDisabled(): ?bool
    {
        return $this->disabled;
    }

    public function isReadonly(): ?bool
    {
        return $this->readonly;
    }

    // Setters
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function setMap(string $map): self
    {
        $this->map = $map;
        return $this;
    }

    public function setDefaultValue(mixed $value): self
    {
        $this->defaultValue = $value;
        return $this;
    }

    public function setRequired(bool $required = true): self
    {
        $this->required = $required;
        return $this;
    }

    public function setPlaceholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function setHint(string $hint): self
    {
        $this->hint = $hint;
        return $this;
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function setAttributes(array $attrs): self
    {
        $this->attributes = $attrs;
        return $this;
    }

    public function setWrapperClass(array $class): self
    {
        $this->wrapperClass = $class;
        return $this;
    }

    public function setInputLayout(string $layout): self
    {
        $this->inputLayout = $layout;
        return $this;
    }

    public function setRightIcon(array $icon = []): self
    {
        $this->rightIcon = $icon;
        return $this;
    }

    public function withRightIcon(): self
    {
        $this->rightIcon = [
            'icon' => 'icon-arrow-down',
            'aria' => 'Arrow down',
        ];
        return $this;
    }

    public function setSearchable(bool $searchable): self
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function setFooter(array $footer): self
    {
        $this->footer = $footer;
        return $this;
    }

    public function setDropzoneKey(string $key): self
    {
        $this->dropzoneKey = $key;
        return $this;
    }

    public function setSearchPlaceholder(string $placeholder): self
    {
        $this->searchPlaceholder = $placeholder;
        return $this;
    }

    public function setHasValue(bool $hasValue): self
    {
        $this->hasValue = $hasValue;
        return $this;
    }

    public function setRows(int $rows): self
    {
        $this->rows = $rows;
        return $this;
    }

    public function setMaxlength(int $maxlength): self
    {
        $this->maxlength = $maxlength;
        return $this;
    }

    public function setStep(string $step): self
    {
        $this->step = $step;
        return $this;
    }

    public function setMultiple(bool $multiple): self
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function setDisabled(bool $disabled): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    public function setReadonly(bool $readonly): self
    {
        $this->readonly = $readonly;
        return $this;
    }

    /**
     * @return null|bool
     */
    public function getNeedBody(): ?bool
    {
        return $this->needBody;
    }

    /**
     * @param null|bool $needBody
     *
     * @return FormFieldConfig
     */
    public function setNeedBody(?bool $needBody): FormFieldConfig
    {
        $this->needBody = $needBody;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPosition(): ?string
    {
        return $this->position;
    }

    /**
     * @param null|string $position
     *
     * @return FormFieldConfig
     */
    public function setPosition(?string $position): FormFieldConfig
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param null|string $id
     *
     * @return FormFieldConfig
     */
    public function setId(?string $id): FormFieldConfig
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return array
     */
    public function getClass(): array
    {
        return $this->class;
    }

    /**
     * @param array $class
     *
     * @return FormFieldConfig
     */
    public function setClass(array $class): FormFieldConfig
    {
        $this->class = $class;

        return $this;
    }

    public static function create(
        string $name = 'text',
        string $type = 'text',
        ?string $label = null,
        ?string $map = null,
        mixed $defaultValue = null,
        bool $required = false,
        ?string $placeholder = null,
        ?string $hint = null,
        array $options = [],
        array $attributes = [],
        array $wrapperClass = [],
        ?string $inputLayout = null,
        ?array $rightIcon = null,
        bool $searchable = false,
        ?array $footer = null,
        ?string $dropzoneKey = null,
        ?string $searchPlaceholder = null,
        ?bool $hasValue = null,
        ?int $rows = null,
        ?int $maxlength = null,
        ?string $step = null,
        ?bool $multiple = null,
        ?bool $disabled = null,
        ?bool $readonly = null,
        ?bool $needBody = null,
        ?string $position = 'right',
        ?string $id = null,
        array $class = [],
    ): self {
        return new self(
            name: $name,
            type: $type,
            label: $label,
            map: $map,
            defaultValue: $defaultValue,
            required:$required,
            placeholder: $placeholder,
            hint: $hint,
            options: $options,
            attributes:$attributes,
            wrapperClass: $wrapperClass,
            inputLayout: $inputLayout,
            rightIcon: $rightIcon,
            searchable: $searchable,
            footer: $footer,
            dropzoneKey: $dropzoneKey,
            searchPlaceholder: $searchPlaceholder,
            hasValue: $hasValue,
            rows: $rows,
            maxlength: $maxlength,
            step: $step,
            multiple: $multiple,
            disabled: $disabled,
            readonly: $readonly,
            needBody: $needBody,
            position: $position,
            id: $id,
            class: $class,
        );
    }
}