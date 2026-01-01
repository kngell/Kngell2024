<?php

declare(strict_types=1);

class SaveProductImageGalleryListener implements EventListenerInterface
{
    public function __construct(private ProductImageGalleryModel $model, private EntityFactoryInterface $factory)
    {
    }

    public function update(EventInterface $event): ?object
    {
        $payload = $event->getParams();
        $productId = $payload['product_id'];
        $formData = $payload['form_data'];
        $imgGalleryUrls = $payload['uploaded_media']['img_gallery'] ?? [];

        $productName = $formData['name'] ?? 'Product Image';

        if (!is_array($imgGalleryUrls) || empty($imgGalleryUrls)) {
            return null;
        }

        $sortOrder = 1;

        $imageGalleryAry = [];
        foreach ($imgGalleryUrls as $url) {
            /** @var ProductImageGallery $galleryEntity */
            $galleryEntity = $this->factory->create(ProductImageGallery::class);

            $galleryEntity->setProductId((int) $productId);
            $galleryEntity->setImageUrl($url);
            $galleryEntity->setSortOrder($sortOrder);
            $defaultAltText = $productName . ' - Gallery Image ' . $sortOrder;
            $galleryEntity->setAltText($defaultAltText);

            $imageGalleryAry[] = $galleryEntity;
            $sortOrder++;
        }
        if ($this->model->save($imageGalleryAry)->isSuccess()) {
            return null;
        }
        return new NullEvent();
    }
}