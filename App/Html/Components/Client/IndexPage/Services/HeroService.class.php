<?php

declare(strict_types=1);

/**
 * @extends AbstractSingleEntityService<Hero>
 */
class HeroService extends AbstractSingleEntityService
{
    public function __construct(
        private HeroModel $model,
        private ImageOptimizerFactory $imageOptimizerFactory,
        HeroCacheManagerFactory $factory,
    ) {
        parent::__construct($factory->create());
    }

    public function getDefaultResponse(): EntityResponseInterface
    {
        return $this->createResponse(
            image: $this->getDefaultImageData(),
            entity: null,
            isDefault: true,
        );
    }

    protected function fetchEntityFromDb(string $page): ?Hero
    {
        $conditions = ['page_target', $page];
        $result = $this->model->one($conditions);
        return $result->isSuccess() ? $result->asClass() : null;
    }

    protected function fetchEntityByIdFromDb(string $id): ?Hero
    {
        $result = $this->model->one(['id', (int) $id]);
        return $result->isSuccess() ? $result->asClass() : null;
    }

    protected function buildResponsiveImage(Entity $hero): array
    {
        $optimizer = $this->imageOptimizerFactory->create();
        $imageUrl = $hero->getImageUrl();

        return [
            'src' => $this->getOptimizedUrl($optimizer, $imageUrl, 1920),
            'srcset' => $this->generateSrcSet($optimizer, $imageUrl, [640, 1024, 1920]),
            'alt' => $hero->getTitle(),
            'width' => 1920,
            'height' => $this->getOptimizedHeight($optimizer, $imageUrl, 1920),
        ];
    }

    protected function getDefaultImageData(): array
    {
        return [
            'src' => '/assets/images/default-hero.jpg',
            'srcset' => '/assets/images/default-hero.jpg 1920w',
            'alt' => 'Default hero image',
            'width' => 1920,
            'height' => 600,
        ];
    }

    protected function createResponse(array $image, ?Entity $entity, bool $isDefault): HeroResponse
    {
        return new HeroResponse($image, $entity, $isDefault);
    }
}