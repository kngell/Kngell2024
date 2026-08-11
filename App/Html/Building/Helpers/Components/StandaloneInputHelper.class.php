<?php

declare(strict_types=1);

final class StandaloneInputHelper implements StandAloneComponentInterface
{
    private array $handlers = [];
    private array $layouts = [];

    public function __construct(
        private readonly HtmlBuilder $htmlBuilder,
    ) {
        $this->initializeDefaults();
    }

    public function build(mixed $params = null): null|array|AbstractHtmlComponent
    {
        $field = $params['field'] ?? null;
        $handlerType = $params['handlerType'] ?? null;
        $layoutType = $params['layoutType'] ?? null;

        $handler = $this->getHandler($handlerType);
        if (!$handler) {
            return null;
        }

        // Handle the field
        $form = $this->htmlBuilder->form();
        $input = $handler->handle($field->toArray(), $form);
        $fieldId = $handler->getFieldId();

        // Get layout
        $layoutType = $layoutType ?? $this->getDefaultLayout($layoutType);
        $layout = $this->getLayout($layoutType);

        // Render with layout
        return $layout->renderInput(
            $field->toArray(),
            $input,
            $fieldId,
            $form,
        );
    }

    private function initializeDefaults(): void
    {
        $this->handlers = [
            'input-field' => new InputFieldHandler(),
            'textarea' => new TextareaFieldHandler(),
            'select' => new NativeSelectFieldHandler(),
            'custom-select' => new CustomSelectFieldHandler(),
            'toggle' => new ToggleSwitchHandler(),
        ];

        // Register default layouts
        $this->layouts = [
            'input' => new FieldLayout(),
            'checkbox' => new FieldCheckboxLayout(),
            'custom-select' => new CustomSelectLayout(),
        ];
    }

    private function getHandler(?string $type = null): ?FieldHandlerInterface
    {
        return $this->handlers[$type] ?? $this->handlers['input-field'];
    }

    private function getLayout(string $type): InputLayoutInterface
    {
        return $this->layouts[$type] ?? new FieldLayout();
    }

    private function getDefaultLayout(?string $type = null): string
    {
        return match ($type) {
            'checkbox', 'radio' => 'checkbox',
            'custom-select' => 'custom-select',
            'toggle' => 'toggle',
            default => 'input',
        };
    }

    public static function create(HtmlBuilder $htmlBuilder): self
    {
        return new self($htmlBuilder);
    }
}