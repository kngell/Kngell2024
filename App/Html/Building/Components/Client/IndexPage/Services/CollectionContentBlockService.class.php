<?php

declare(strict_types=1);

/**
 * @extends AbstractCollectionEntityService<ContentBlockShow>
 */
class CollectionContentBlockService extends AbstractCollectionEntityService
{
    public function __construct(
        private ContentBlockShowModel $model,
        private ImageOptimizerFactory $imageOptimizerFactory,
        private BlockType $blockType,
        ContentBlockCacheManagerFactory $factory,
        private HtmlSectionPresentationService $presenter,
    ) {
        parent::__construct($factory->create());
    }

    /**
     * Get banners organized by position for easy template rendering.
     *
     * @return array
     */
    public function getOrganizedForPage(?string $page = null, array $widthOverrides = []): array
    {
        $responses = $this->getForPage($page);
        $positionEnum = $this->blockType->getPositionEnum();
        return $positionEnum::getOrganized($responses);
    }

    public function getDefaultResponse(): array
    {
        $positions = [
            SmallBannerPosition::LEFT_WIDE->value,
            SmallBannerPosition::LEFT_SQUARE_LIGHT->value,
            SmallBannerPosition::LEFT_SQUARE_DARK->value,
            SmallBannerPosition::RIGHT->value,
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

    protected function createResponse(array $image, ?Entity $entity, bool $isDefault): ContentBlockCollectionResponse
    {
        return new ContentBlockCollectionResponse($image, $entity, $isDefault, $this->presenter);
    }

    protected function fetchEntitiesFromDbForPage(string $page): array
    {
        //JSON_UNQUOTE(JSON_EXTRACT(block_metadata, "$.position"))
        //block_metadata->>"$.position"
        $conditions = array_merge(
            ['page_target', $page,
                'OR', 'page_target is null',
                'AND', 'block_type' => $this->blockType->value,
                'AND', 'JSON_UNQUOTE(JSON_EXTRACT(block_metadata, "$.position"))' => $this->blockType->getAllValues(),
            ],
            [
                ConditionListMode::MODE_FRONTEND->value => true,
                SpecialConditions::CASE->value => ['page_target' => $page],
            ],
        );
        $dbResult = $this->model->all($conditions, true);
        $result = $dbResult->asClass();
        return $dbResult->isSuccess() ? $result : [];
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

    protected function buildResponsiveImageForEntity(ContentBlockShow $entity): array
    {
        $optimizer = $this->imageOptimizerFactory->create();
        $imageUrl = $entity->get('image')['url'];
        $position = $entity->get('position');
        $bannerEnum = $entity->getBlockType()->getPositionEnum();
        $widths = $bannerEnum::tryFrom($position)?->getWidths($this->widths);

        if ($imageUrl === null || (is_array($imageUrl) && $imageUrl[0] === '')) {
            return [];
        }
        if (is_array($imageUrl)) {
            $imgOptimizedArray = [];
            foreach ($imageUrl as $url) {
                $imgOptimizedArray[] = [
                    'src' => $this->getOptimizedUrl($optimizer, $url, $widths['desktop']),
                    'srcset' => $this->generateSrcSet($optimizer, $url, array_values($widths)),
                    'alt' => $entity->getTitle(),
                    'width' => $widths['desktop'],
                    'height' => $this->getOptimizedHeight($optimizer, $url, $widths['desktop']),
                ];
            }
            return $imgOptimizedArray;
        }
        return [
            'src' => $this->getOptimizedUrl($optimizer, $imageUrl, $widths['desktop']),
            'srcset' => $this->generateSrcSet($optimizer, $imageUrl, array_values($widths)),
            'alt' => $entity->getTitle(),
            'width' => $widths['desktop'],
            'height' => $this->getOptimizedHeight($optimizer, $imageUrl, $widths['desktop']),
        ];
    }

    protected function getDefaultImageDataForPosition(string $position): array
    {
        $bannerbox = SmallBannerPosition::tryFrom($position);
        return [
            'src' => '/storage/cache/pages/images/default-banner.jpg',
            'srcset' => '/storage/cache/pages/images/default-banner.jpg ' . $bannerbox->getWidth('desktop', $this->widths) . 'w',
            'alt' => 'Default banner',
            'width' => $bannerbox->getWidth('desktop', $this->widths),
            'height' => 600,
        ];
    }
}