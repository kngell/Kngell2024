<?php

declare(strict_types=1);

use Psr\Log\LoggerInterface;

final class DeleteProductVariationListener extends AbstractEntityDeletionListener
{
    public function __construct(
        private ProductVariationModel $productVariationModel,
        private VariationAttributeModel $variationAttributeModel,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($logger);
    }

    protected function expectedEntityClass(): string
    {
        return Product::class;
    }

    protected function entityType(): string
    {
        return 'product';
    }

    protected function performDeletion(
        int|string $entityId,
        string $deletionOption,
        array $payload,
    ): DeletionResultInterface {
        // Hard delete is handled entirely by FK ON DELETE CASCADE.
        // Nothing to do here for 'permanent' mode.
        if ($deletionOption !== 'archive') {
            return new VariationDeletionResult(
                entityId:     (int) $entityId,
                affectedRows: 0,
                changed:      false,
                deletionMode: $deletionOption,
            );
        }

        $productId = (int) $entityId;

        $rows = $this->productVariationModel
            ->all(['product_id' => $productId])
            ->asArray();

        $variationIds = $this->collectActiveVariationIds($rows);
        $params = [
            'data' => $rows,
            'deleteOption' => $deletionOption,
        ];
        if (empty($rows)) {
            return new VariationDeletionResult(
                entityId: $productId,
                affectedRows: 0,
                changed: false,
                deletionMode: 'archive',
                variationsDeleted: 0,
                attributesDeleted:0,
            );
        }
        $varResult = $this->productVariationModel->delete($params);

        $this->assertSuccess($varResult, 'variations', $productId);

        $attributesArchived = 0;
        if ($variationIds !== []) {
            $attrResult = $this->variationAttributeModel->delete([
                'variation_id' => $variationIds,
                'deleteOption' => $deletionOption,
            ]);
            $this->assertSuccess($attrResult, 'variation attributes', $productId);
            $attributesArchived = $attrResult->getRowCount();
        }

        return new VariationDeletionResult(
            entityId: $productId,
            affectedRows: $varResult->getRowCount() + $attributesArchived,
            changed: $varResult->hasChanged() || $attributesArchived > 0,
            deletionMode: 'archive',
            variationsDeleted: $varResult->getRowCount(),
            attributesDeleted: $attributesArchived,
        );
    }

    /**
     * @return list<int>
     */
    private function collectActiveVariationIds(array $rows): array
    {
        return array_values(array_filter(
            array_map('intval', array_column($rows, 'id')),
            static fn (int $id): bool => $id > 0,
        ));
    }
}