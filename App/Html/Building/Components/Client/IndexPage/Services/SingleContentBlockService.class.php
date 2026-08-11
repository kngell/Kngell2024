<?php

declare(strict_types=1);

/**
 * @extends AbstractSingleEntityService<ContentBlock>
 */
class SingleContentBlockService extends AbstractSingleEntityService
{
    public function __construct(
        private ContentBlockModel $model,
        private ImageOptimizerFactory $imageOptimizerFactory,
        private BlockType $blockType,
        ContentBlockCacheManagerFactory $factory,
        private readonly HtmlSectionPresentationService $presenter,
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

    protected function fetchEntityFromDb(string $page): ?ContentBlock
    {
        $conditions = array_merge(
            ['page_target', $page,
                'OR', 'page_target is null',
                'AND', 'block_type' => $this->blockType->value,
            ],
            [
                ConditionListMode::MODE_FRONTEND->value => true,
                SpecialConditions::CASE->value => ['page_target' => $page],
            ],
        );
        $result = $this->model->one($conditions);
        return $result->isSuccess() ? $result->asClass() : null;
    }

    protected function fetchEntityByIdFromDb(string $id): ?ContentBlock
    {
        $result = $this->model->one(['id', (int) $id]);
        return $result->isSuccess() ? $result->asClass() : null;
    }

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

    protected function createResponse(array $image, ?Entity $entity, bool $isDefault): ContentBlockResponse
    {
        return new ContentBlockResponse($image, $entity, $this->presenter, $isDefault);
    }

    private function getImageUrl(ContentBlock $entity): string|array
    {
        $image = $entity->get('image');
        $blockType = $entity->getBlockType();
        return match ($blockType) {
            BlockType::HERO => $image['url'] ?? '',
            BlockType::SUMMER_BANNER => $image,
            default => '',
        };
    }
}