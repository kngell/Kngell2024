<?php

declare(strict_types=1);

final class ProductStatusOptionsService implements SelectOptionsServiceInterface
{
    private const string SELECT_LABLE = '-- Select a Status --';
    private const string ENTITY = 'ProductStatus';

    public function __construct(
        private ProductStatusModel $productStatusModel,
    ) {
    }

    public function getActiveOptions(): array
    {
        try {
            $statuses = $this->productStatusModel->getActiveStatuses();

            $options = ['' => self::SELECT_LABLE];

            foreach ($statuses as $status) {
                // Ensure the entity is valid and has required methods
                if ($status instanceof ProductStatus) {
                    $options[$status->getId()] = $this->formatLabel($status);
                }
            }
            return $options;
        } catch (QueryResultException $e) {
            error_log('ProductStatusService: Failed to load Product Status - ' . $e->getMessage());
            return $this->getDefaultOption();
        }
    }

    private function formatLabel(ProductStatus $status): string
    {
        $code = $status->getStatusCode()->value;
        $name = $status->getName();
        return "{$name} ({$code})";
    }

    // Optional: Hardcoded fallback if the database fails
    private function getDefaultOption(): array
    {
        return [
            '' => self::SELECT_LABLE,
            '1' => 'draft',
        ];
    }
}