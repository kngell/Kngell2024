<?php

declare(strict_types=1);

final class ActionDefinition
{
    public function __construct(
        public readonly string $action,
        public readonly string $method,
        public readonly string $icon,
        public readonly string $iconLabel,
        public readonly array $iconClasses,
        public readonly string $buttonType,
        public readonly string $screenReaderText,
        public readonly string $actionClass,
        public readonly array $buttonCustom = [],
        public readonly bool $csrfProtected = true,
        public readonly string $idField = 'public_id',
        public readonly ?string $blockType = null,
    ) {
    }
}
