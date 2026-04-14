<?php

declare(strict_types=1);

interface BulkRowInterface
{
    /**
     * Get the SQL rule for this bulk operation.
     *
     * @param array $rowValuesData Normalized row data
     * @param AbstractRules $rules The rule instance (for createParameter/createLiteralValue)
     *
     * @return string SQL rule
     */
    public function getRule(array $rowValuesData, AbstractRules $rules): string;

    /**
     * Get column list if applicable (for temp tables).
     */
    public function getColumnList(): ?string;

    /**
     * Check if this strategy is supported for the given entity manager.
     */
    public function supports(EntityManagerInterface $em): bool;
}
