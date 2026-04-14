<?php

declare(strict_types=1);

class HeroModel extends AbstractSaveModel
{
    private const string IMG_URL = 'image_url';

    public function save(null|array|Entity $data = null, array $conditions = []): QueryResult
    {
        return parent::save($data, $conditions);
    }

    public function getHero(int $heroId): ?Hero
    {
        return $this->one(['hero_id' => $heroId])?->asClass();
    }

    public function getImagesPath(): array
    {
        $params['columns'] = ['image_url'];
        return $this->all($params)->asArray();
    }

    protected function generateMissingFields(array $data): array
    {
        return $this->generatePublicId($data);
    }

    protected function validateData(array $data): void
    {
    }
}