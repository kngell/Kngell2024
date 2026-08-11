<?php

declare(strict_types=1);

trait BlockTypeTrait
{
    private function resolveBlockType(EventInterface $event): ?BlockType
    {
        $payload = $event->getParams();
        $blockType = $payload['context']['block_type'] ?? '';
        if (!empty($blockType)) {
            return BlockType::tryFrom($blockType);
        }
        $blockType = $event->getData()->getBlockType();
        if (empty($blockType)) {
            return null;
        }
        return BlockType::tryFrom($blockType);
    }
}