<?php

declare(strict_types=1);

/**
 * @extends AbstractCollectionEntityService<FooterMenuShow>
 */
class FooterService extends AbstractCollectionEntityService
{
    public function __construct(
        private FooterMenuShowModel $model,
        private ImageOptimizerFactory $imageOptimizerFactory,
        FooterMenuCacheManagerFactory $factory,
        private HtmlSectionPresentationService $presenter,
    ) {
        parent::__construct($factory->create());
    }

    public function getDefaultResponse(): array
    {
        return [
            $this->createResponse(
                image: $this->getDefaultImageData(),
                entity: null,
                isDefault: true,
            ),
        ];
    }

    protected function fetchEntitiesFromDbForPage(string $page): array
    {
        $conditions = [
            'is_active' => true,
            ConditionListMode::MODE_FRONTEND->value => true,
        ];

        if ($page !== 'index') {
            $conditions['page_target'] = $page;
        }

        $result = $this->model->all($conditions);

        if (!$result->isSuccess()) {
            return [];
        }

        return $result->asClass();
    }

    #[Override]
    protected function fetchEntitiesFromDbByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $result = $this->model->all(['id' => ['IN', $ids]]);

        if (!$result->isSuccess()) {
            return [];
        }

        return $result->asClass();
    }

    #[Override]
    protected function buildResponses(array $entities): array
    {
        $responses = [];

        foreach ($entities as $entity) {
            $responses[] = $this->createResponse(
                image: $this->buildResponsiveImage($entity),
                entity: $entity,
                isDefault: false,
            );
        }

        return $responses;
    }

    /**
     * @param FooterMenuShow $entity
     *
     * @return array
     */
    protected function buildResponsiveImage(Entity $entity): array
    {
        $optimizer = $this->imageOptimizerFactory->create();
        $imageUrl = $this->getImageUrl($entity);

        if (empty($imageUrl)) {
            return $this->getDefaultImageData();
        }

        if (is_array($imageUrl)) {
            $imgResponse = [];
            foreach ($imageUrl as $key => $singleImg) {
                $imgResponse[$key] = [
                    'src' => $this->getOptimizedUrl($optimizer, $singleImg, 1920),
                    'srcset' => $this->generateSrcSet($optimizer, $singleImg, [640, 1024, 1920]),
                    'alt' => $entity->getTitle(),
                    'width' => 1920,
                    'height' => $this->getOptimizedHeight($optimizer, $singleImg, 1920),
                ];
            }
            return $imgResponse;
        }

        return [
            'src' => $this->getOptimizedUrl($optimizer, $imageUrl, 1920),
            'srcset' => $this->generateSrcSet($optimizer, $imageUrl, [640, 1024, 1920]),
            'alt' => $entity->getTitle(),
            'width' => 1920,
            'height' => $this->getOptimizedHeight($optimizer, $imageUrl, 1920),
        ];
    }

    protected function getDefaultImageData(): array
    {
        return [
            'src' => '/public/assets/img/ecommerce/default.png',
            'srcset' => '/public/assets/img/ecommerce/default.png 1920w',
            'alt' => 'Default image',
            'width' => 1920,
            'height' => 600,
        ];
    }

    protected function createResponse(array $image, ?Entity $entity, bool $isDefault): FooterResponse
    {
        return new FooterResponse($image, $this->presenter, $entity, $isDefault);
    }

    private function getImageUrl(FooterMenuShow $entity): null|string|array
    {
        if (method_exists($entity, 'getImageUrl')) {
            return $entity->getImageUrl();
        }
        return null;
    }
}