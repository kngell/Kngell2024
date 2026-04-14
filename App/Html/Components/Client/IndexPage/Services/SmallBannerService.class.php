<?php

declare(strict_types=1);

/**
 * @extends AbstractCollectionEntityService<SmallBannerShow>
 */
class SmallBannerService extends AbstractCollectionEntityService
{
    public function __construct(
        private SmallBannerShowModel $model,
        ImageOptimizerFactory $imageOptimizerFactory,
        SmallBannerCacheManagerFactory $factory,
    ) {
        parent::__construct($imageOptimizerFactory, $factory->create());
    }

    /**
     * Get banners organized by position for easy template rendering.
     *
     * @return array
     */
    public function getOrganizedForPage(?string $page = null, array $widthOverrides = []): array
    {
        $responses = $this->getForPage($page);
        return SmallBannerClass::getOrganized($responses);
    }

    public function getDefaultResponse(): array
    {
        $positions = [
            SmallBannerClass::LEFT_WIDE->value,
            SmallBannerClass::SQUARE_LIGHT->value,
            SmallBannerClass::SQUARE_DARK->value,
            SmallBannerClass::RIGHT->value,
        ];

        $responses = [];
        foreach ($positions as $position) {
            $responses[] = $this->createResponse(
                image: $this->getDefaultImageDataForPosition($position),
                entity: null,
                isDefault: true,
            );
        }
        return $responses;
    }

    protected function createResponse(array $image, ?Entity $entity, bool $isDefault): SmallBannerResponse
    {
        return new SmallBannerResponse($image, $entity, $isDefault);
    }

    protected function fetchEntitiesFromDbForPage(string $page): array
    {
        $dbResult = $this->model->all(['page_target', $page], true);
        return $dbResult->isSuccess() ? $dbResult->asClass() : [];
    }

    protected function fetchEntitiesFromDbByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        $dbResult = $this->model->all(['id', 'in', $ids]);
        return $dbResult->isSuccess() ? $dbResult->asClass() : [];
    }

    protected function buildResponses(array $entities): array
    {
        $responses = [];
        foreach ($entities as $entity) {
            $responses[] = $this->createResponse(
                image: $this->buildResponsiveImageForEntity($entity),
                entity: $entity,
                isDefault: false,
            );
        }
        return $responses;
    }

    protected function buildResponsiveImageForEntity(SmallBannerShow $banner): array
    {
        $optimizer = $this->imageOptimizerFactory->create();
        $imageUrl = $banner->getCustomImageUrl();
        $widths = $banner->getSmallBannerClass()->getWidths($this->widths);

        if ($imageUrl === null) {
            return [];
        }

        return [
            'src' => $this->getOptimizedUrl($optimizer, $imageUrl, $widths['desktop']),
            'srcset' => $this->generateSrcSet($optimizer, $imageUrl, array_values($widths)),
            'alt' => $banner->getCustomTitle(),
            'width' => $widths['desktop'],
            'height' => $this->getOptimizedHeight($optimizer, $imageUrl, $widths['desktop']),
        ];
    }

    protected function getDefaultImageDataForPosition(string $position): array
    {
        $bannerbox = SmallBannerClass::tryFrom($position);
        return [
            'src' => '/storage/cache/pages/images/default-banner.jpg',
            'srcset' => '/storage/cache/pages/images/default-banner.jpg ' . $bannerbox->getWidth('desktop', $this->widths) . 'w',
            'alt' => 'Default banner',
            'width' => $bannerbox->getWidth('desktop', $this->widths),
            'height' => 600,
        ];
    }
}