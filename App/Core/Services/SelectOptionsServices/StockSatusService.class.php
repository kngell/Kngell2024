<?php

declare(strict_types=1);

final class StockStatusService
{
    // Inject the model responsible for database access
    public function __construct(
        private StockStatusModel $stockStatusModel,
    ) {
    }

    /**
     * Retrieves stock statuses from the database, formatted for a dropdown.
     *
     * @return array<string, string>
     */
    public function getStockStatusOptions(): array
    {
        try {
            /** @var StockStatus[] $statuses */
            $statuses = $this->stockStatusModel->all()->asClass();

            $options = ['' => '-- Select stock status --'];

            foreach ($statuses as $status) {
                if ($status instanceof StockStatus) {
                    // Use the status code as the dropdown value and the label for display
                    $options[$status->getId()] = $status->getStockStatusCode()->value;
                }
            }
            return $options;
        } catch (QueryResultException $e) {
            error_log('StockStatusService: Failed to load stock statuses - ' . $e->getMessage());
            // Fallback to hardcoded values if the database connection fails
            return $this->getDefaultStockStatusOptions();
        }
    }

    // Fallback method
    private function getDefaultStockStatusOptions(): array
    {
        return [
            '' => '-- Select stock status --',
            '1' => 'In Stock',
            '2' => 'Out of Stock',
        ];
    }
}