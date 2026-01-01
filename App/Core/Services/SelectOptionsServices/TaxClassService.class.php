<?php

declare(strict_types=1);

final class TaxClassService
{
    public function __construct(
        private TaxClassModel $taxClassModel, // Inject the model
    ) {
    }

    /**
     * Get all active tax classes formatted for dropdown options.
     * The array keys are the TaxClass ID, and the values are the formatted labels.
     */
    public function getActiveTaxClassOptions(): array
    {
        try {
            // Assuming your model has a method to fetch active tax classes
            $taxClasses = $this->taxClassModel->getActiveTaxClasses();

            $options = ['' => '-- Select a VAT class --'];

            // Loop through entities (assuming they are TaxClass entities)
            foreach ($taxClasses as $taxClass) {
                // Ensure the entity is valid and has required methods
                if ($taxClass instanceof TaxClass) {
                    $options[$taxClass->getId()] = $this->formatTaxClassLabel($taxClass);
                }
            }
            return $options;
        } catch (QueryResultException $e) {
            error_log('TaxClassService: Failed to load tax classes - ' . $e->getMessage());
            // Return a fallback or throw a more specific application exception
            return $this->getDefaultTaxClassOptions();
        }
    }

    private function formatTaxClassLabel(TaxClass $taxClass): string
    {
        // Format the output: e.g., "Standard VAT (STND)"
        $code = $taxClass->getCode();
        $label = $taxClass->getLabel();

        return "{$label} ({$code})";
    }

    // Optional: Hardcoded fallback if the database fails
    private function getDefaultTaxClassOptions(): array
    {
        return [
            '' => '-- Select a VAT class --',
            '1' => 'Standard',
        ];
    }
}