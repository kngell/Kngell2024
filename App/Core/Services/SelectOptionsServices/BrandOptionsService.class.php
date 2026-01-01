<?php

declare(strict_types=1);

class BrandOptionsService implements SelectOptionsServiceInterface
{
    private const string SELECT_LABLE = '-- Select a Brand --';

    public function __construct(
        private BrandModel $brandModel,
    ) {
    }

    public function getActiveOptions(): array
    {
        try {
            $brands = $this->brandModel->all(['is_active', true])->asClass();

            $options = ['' => self::SELECT_LABLE];

            foreach ($brands as $brand) {
                // Ensure the entity is valid and has required methods
                if ($brand instanceof Brand) {
                    $options[$brand->getId()] = $this->formatLabel($brand);
                }
            }
            return $options;
        } catch (QueryResultException $e) {
            error_log('ProductStatusService: Failed to load Product Status - ' . $e->getMessage());
            return $this->getDefaultOption();
        }
    }

    private function formatLabel(Brand $brand): string
    {
        return $brand->getName();
    }

    // Optional: Hardcoded fallback if the database fails
    private function getDefaultOption(): array
    {
        return [
            '' => self::SELECT_LABLE,
            '1' => 'Apple',
        ];
    }
}