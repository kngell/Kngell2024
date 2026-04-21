<?php

declare(strict_types=1);

final readonly class HeaderButtonConfig
{
    public function __construct(
        public string $action,
        public ButtonConfig $button,
        public bool $requiresEditMode = false,
        public bool $requiresEntityId = false,
        public ?string $formName = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge($this->button->toArray(), [
            'action' => $this->action,
            'requiresEditMode' => $this->requiresEditMode,
            'requiresEntityId' => $this->requiresEntityId,
            'formName' => $this->formName,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        // Extract form-level config
        $action = $data['action'] ?? '';
        $requiresEditMode = $data['requiresEditMode'] ?? false;
        $requiresEntityId = $data['requiresEntityId'] ?? false;
        $formName = $data['formName'] ?? null;

        // Everything else goes to ButtonConfig
        $buttonData = array_diff_key($data, array_flip([
            'action',
            'requiresEditMode',
            'requiresEntityId',
            'formName',
        ]));

        return new self(
            action: $action,
            button: ButtonConfig::fromArray($buttonData),
            requiresEditMode: $requiresEditMode,
            requiresEntityId: $requiresEntityId,
            formName: $formName,
        );
    }
}