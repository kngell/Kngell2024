<?php

declare(strict_types=1);

class SmallBannerModel extends AbstractSaveModel
{
    public function getProductsWithColumns(string ...$columns): array
    {
        $params = [
            'columns' => $columns,
        ];
        $results = $this->all($params);
        if ($results->isSuccess()) {
            return $results->asArray();
        }
        return [];
    }

    public function getImagesPath(): array
    {
        $params['columns'] = ['custom_image_url'];
        return $this->all($params)->asArray();
    }

    protected function validateData(array $data): void
    {
    }

    protected function generateMissingFields(array $data): array
    {
        $data['is_active'] = isset($data['is_active']) ? true : false;
        return $this->generatePublicId($data);
    }
}