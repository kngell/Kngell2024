<?php

declare(strict_types=1);

final class ProductStatusOptionsService extends AbstractSelectOptionsService
{
    protected const string SELECT_LABEL = '-- Select a Status --';

    protected ?string $entityClass = ProductStatus::class;

    public function __construct(
        private ProductStatusModel $model,
    ) {
    }

    protected function fetchOptions(bool $active = true): array
    {
        $conditions = $active ? ['is_active', true] : [];
        $statuses = $this->model->all($conditions)->asClass();
        return $this->processEntities($statuses);
    }

    protected function formatLabel(object $entity): string
    {
        if (!$entity instanceof ProductStatus) {
            return '';
        }

        $code = $entity->getStatusCode()->value;
        $name = $entity->getName();
        return "{$name} ({$code})";
    }

    protected function getDefaultOptions(): array
    {
        return [
            '' => self::SELECT_LABEL,
            '1' => 'draft',
        ];
    }
}