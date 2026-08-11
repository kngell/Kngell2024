<?php

declare(strict_types=1);

final class TaxClassService extends AbstractSelectOptionsService
{
    protected const string SELECT_LABEL = '-- Select a VAT class --';

    protected ?string $entityClass = TaxClass::class;

    public function __construct(
        private TaxClassModel $model,
    ) {
    }

    protected function fetchOptions(bool $active = true): array
    {
        $conditions = $active ? ['active', true] : [];
        $taxClasses = $this->model->all($conditions)->asClass();
        return $this->processEntities($taxClasses);
    }

    protected function formatLabel(object $entity): string
    {
        if (!$entity instanceof TaxClass) {
            return '';
        }

        $code = $entity->getCode();
        $label = $entity->getLabel();
        return "{$label} ({$code})";
    }

    protected function getDefaultOptions(): array
    {
        return [
            '' => self::SELECT_LABEL,
            '1' => 'Standard',
        ];
    }
}