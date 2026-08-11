<?php

declare(strict_types=1);

/**
 * @extends AbstractCollectionEntityService<Category>
 */
class CategoryFrontendService extends AbstractCollectionEntityService
{
    public function __construct(
        private CategoryModel $model,
        private ImageOptimizerFactory $imageOptimizerFactory,
        CategoryCacheManagerFactory $factory,
    ) {
        parent::__construct($factory->create());
    }

    public function getOrganizedForPage(?string $page = null): array
    {
        $responses = $this->getForPage($page);
        return $this->organizeCategoriesByPosition($responses);
    }

    public function getDefaultResponse(): array
    {
        // Create one default category response for consistency
        return [
            $this->createResponse(
                image: $this->getDefaultImageData(),
                entity: null,
                isDefault: true,
            ),
        ];
    }

    protected function createResponse(array $image, ?Entity $entity, bool $isDefault): CategoryFrontendResponse
    {
        return new CategoryFrontendResponse($image, $entity, $isDefault);
    }

    protected function fetchEntitiesFromDbForPage(string $page): array
    {
        $conditions = $this->buildConditionsForPage($page);
        $result = $this->model->all($conditions);

        return $result->isSuccess() ? $result->asClass() : [];
    }

    protected function fetchEntitiesFromDbByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $result = $this->model->all(['id', 'in', $ids]);
        return $result->isSuccess() ? $result->asClass() : [];
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

    protected function buildResponsiveImageForEntity(Category $category): array
    {
        $optimizer = $this->imageOptimizerFactory->create();
        $imageUrl = $category->getImageUrl();

        if ($imageUrl === null) {
            return $this->getDefaultImageData();
        }

        $widths = $this->getWidthsForCategory($category);

        return [
            'fallback' => [
                'src' => $this->getOptimizedUrl($optimizer, $imageUrl, $widths['desktop']),
                'srcset' => $this->generateSrcSet($optimizer, $imageUrl, array_values($widths)),
                'alt' => $category->getName(),
                'width' => $widths['desktop'],
                'height' => $this->getOptimizedHeight($optimizer, $imageUrl, $widths['desktop']),
            ],
        ];
    }

    protected function getDefaultImageData(): array
    {
        return [
            'fallback' => [
                'src' => '/assets/images/default-category.jpg',
                'srcset' => '/assets/images/default-category.jpg 400w',
                'alt' => 'Category image',
                'width' => 400,
                'height' => 300,
            ],
        ];
    }

    // ==================== PRIVATE HELPER METHODS ====================

    private function buildConditionsForPage(string $page): array
    {
        $conditions = [
            'is_active' => true,
            'icon IS NOT NULL',
            'ORDER BY' => 'order_index ASC, name ASC',
        ];

        switch ($page) {
            case 'featured':
                $conditions['is_featured'] = true;
                // $conditions['has_image'] = true;
                $conditions['limit'] = 6;
                break;
            case 'popular':
                $conditions['ORDER BY'] = 'view_count DESC, order_index ASC';
                $conditions['limit'] = 8;
                break;
            case 'sidebar':
                // $conditions['show_in_sidebar'] = true;
                $conditions['limit'] = 5;
                break;
            case 'homepage':
            default:
                $conditions['limit'] = 10;
                break;
        }

        return $conditions;
    }

    private function getWidthsForCategory(Category $category): array
    {
        if ($this->widths) {
            return $this->widths;
        }

        return [
            'mobile' => 200,
            'tablet' => 300,
            'desktop' => 400,
        ];
    }

    private function organizeCategoriesByPosition(array $responses): array
    {
        $organized = [];

        foreach ($responses as $response) {
            $category = $response->getCategory();

            if ($category instanceof Category) {
                $position = 'default'; //$category->getSliderPosition() ?? 'default';

                if (!isset($organized[$position])) {
                    $organized[$position] = [];
                }

                $organized[$position][] = $response;
            }
        }

        return $organized;
    }
}