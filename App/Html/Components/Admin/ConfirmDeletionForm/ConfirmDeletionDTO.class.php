<?php

declare(strict_types=1);

class ConfirmDeletionDTO
{
    public function __construct(
        public readonly array $id,
        public readonly string $label,
        public readonly string $deleteRoute,
        public readonly string $cancelRoute,
        public readonly string $subtitle,
        public readonly array $entitySummary = [],
        public readonly bool $isVisible = false,
        public readonly bool $isAjax = false,
        public readonly array $metadata = [],
    ) {
    }

    public function toFormValues(): array
    {
        return [
            'id' => $this->id,
            'confirmed' => '1',
            'delete_option' => 'archive',
            'label' => $this->label,
            'cancel_route' => $this->cancelRoute,
            'entity_summary' => $this->entitySummary,
            'deletion_impacts' => [],
            'confirm_label' => 'I understand this '
                . strtolower($this->label)
                . ' will be affected',
            'metadata' => $this->metadata,
            'is_ajax' => $this->isAjax,
        ];
    }

    public static function fromFlashData(
        array $flashData,
        string $label,
        string $deleteRoute,
        string $cancelRoute,
        bool $isVisible = false,
        bool $isAjax = false,
        ?callable $subtitleBuilder = null,
    ): self {
        $name = $flashData['name'] ?? 'Unknown';

        $defaultSubtitle = sprintf(
            'This action will permanently affect <strong>%s</strong>. '
            . 'This cannot be undone.',
            htmlspecialchars($name),
        );

        return new self(
            id: $flashData['id'],
            label: $label,
            deleteRoute: $deleteRoute,
            cancelRoute: $cancelRoute,
            subtitle: $subtitleBuilder
                ? $subtitleBuilder($flashData)
                : $defaultSubtitle,
            entitySummary: self::buildSummary($flashData),
            isVisible: $isVisible,
            isAjax: $isAjax,
            metadata: $flashData['metadata'] ?? [],
        );
    }

    private static function buildSummary(array $flashData): array
    {
        $metadata = $flashData['metadata'] ?? [];
        $summary = [];

        if (!empty($flashData['name'])) {
            $summary['name'] = $flashData['name'];
        }

        if (!empty($flashData['image'])) {
            $summary['image'] = $flashData['image'];
        }

        if (!empty($metadata['sku'])) {
            $summary['sku'] = $metadata['sku'];
        }

        if (isset($metadata['stock_quantity'])) {
            $summary['stock'] = $metadata['stock_quantity'] . ' units';
        }

        $description = $metadata['short_description']
            ?? $metadata['description']
            ?? null;

        if (!empty($description)) {
            $summary['description'] = $description;
        }

        return $summary;
    }
}