<?php

declare(strict_types=1);

final class StockStatusService extends AbstractSelectOptionsService
{
    protected const string SELECT_LABEL = '-- Select stock status --';

    protected ?string $entityClass = StockStatus::class;

    public function __construct(
        private StockStatusModel $model,
    ) {
    }

    protected function fetchOptions(bool $active = true): array
    {
        $statuses = $this->model->all()->asClass();
        return $this->processEntities($statuses);
    }

    protected function formatLabel(object $entity): string
    {
        if (!$entity instanceof StockStatus) {
            return '';
        }
        return $entity->getStockStatusCode()->value;
    }

    protected function getDefaultOptions(): array
    {
        return [
            '' => self::SELECT_LABEL,
            '1' => 'In Stock',
            '2' => 'Out of Stock',
        ];
    }
}