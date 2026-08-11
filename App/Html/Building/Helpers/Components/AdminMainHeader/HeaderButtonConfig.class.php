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
        public string $method = 'POST',
        public ?string $blockType = null,
        public bool $showActions = true,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return array_merge($this->button->toArray(), [
            'action' => $this->action,
            'requiresEditMode' => $this->requiresEditMode,
            'requiresEntityId' => $this->requiresEntityId,
            'formName' => $this->formName,
            'method' => $this->method,
            'block_type' => $this->blockType,
            'showActions' => $this->showActions,
        ]);
    }

    /**
     * Smart constructor — accepts either a legacy array or a HeaderButton.
     *
     * @param array<string, mixed>|HeaderButton $data
     */
    public static function from(array|HeaderButton $data): self
    {
        return self::fromArray($data instanceof HeaderButton ? $data->toArray() : $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $action = $data['action'] ?? '';
        $requiresEditMode = $data['requiresEditMode'] ?? false;
        $requiresEntityId = $data['requiresEntityId'] ?? false;
        $formName = $data['formName'] ?? null;
        $method = $data['method'] ?? 'POST';
        $blockType = $data['block_type'] ?? null;
        $showActions = $data['showActions'] ?? true;

        $buttonData = array_diff_key($data, array_flip([
            'action',
            'method',
            'requiresEditMode',
            'requiresEntityId',
            'formName',
            'block_type',
            'showActions',
        ]));

        return new self(
            action: $action,
            method: $method,
            button: ButtonConfig::fromArray($buttonData),
            requiresEditMode: $requiresEditMode,
            requiresEntityId: $requiresEntityId,
            formName: $formName,
            blockType: $blockType,
            showActions: $showActions,
        );
    }
}