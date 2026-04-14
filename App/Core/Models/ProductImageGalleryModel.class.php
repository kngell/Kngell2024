<?php

declare(strict_types=1);

class ProductImageGalleryModel extends Model
{
    public function getGalleryByPaths(): array
    {
        $query = $this->em->setEntity($this->entity)->createQueryBuilder();

        $query->select('image_url')
            ->where('image_url', '!=', '')
            ->whereNotNull('image_url')
            ->build();
        return $this->em->persist()->getQueryResult()->asArray();
    }
}
