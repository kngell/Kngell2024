<?php

declare(strict_types=1);

final class ContentBlockPaginatedAdapter extends AbstractPaginatedAdapter
{
    protected string $identifierPrefix = 'c_';
    protected array $sort = ['sort_order' => 'ASC'];
    private string $blockType = '';

    public function __construct(
        Model $model,
        string $blockType = '',
        array $filters = [],
        array $sort = ['sort_order' => 'ASC'],
    ) {
        $this->blockType = $blockType;
        parent::__construct($model, $filters, $sort);
    }

    public function getEntityClass(): string
    {
        $block = BlockType::tryFrom($this->blockType);
        return match ($block) {
            BlockType::HERO => ContentBlock::class,
            BlockType::SMALL_BANNER => ContentBlockShow::class,
            default => ContentBlock::class
        };
    }

    public function getTotalCount(): int
    {
        if (empty($this->blockType)) {
            throw new InvalidArgumentException('BlockType is required');
        }

        return parent::getTotalCount();
    }

    public function getAllKeys(int $page, int $perPage): array
    {
        if (empty($this->blockType)) {
            throw new InvalidArgumentException('BlockType is required');
        }

        return parent::getAllKeys($page, $perPage);
    }

    protected function buildConditions(): array
    {
        if (empty($this->blockType)) {
            throw new InvalidArgumentException('BlockType is required');
        }

        $conditions = parent::buildConditions();
        $conditions['block_type'] = $this->blockType;

        return $conditions;
    }
}