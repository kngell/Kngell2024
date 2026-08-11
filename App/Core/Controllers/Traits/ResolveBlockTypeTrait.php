<?php

declare(strict_types=1);

/** @property Request $request
 * @method string getRedirectUrl()
 */
trait ResolveBlockTypeTrait
{
    protected ?BlockType $blockType = null;

    private function resolveBlockType(?string $blockType = null, ?Request $request = null): void
    {
        $request = $request ?? $this->request;
        $blockType = $blockType ?? $request->get('block_type');

        if (empty($blockType)) {
            $this->blockType = null;
            return;
        }
        $this->blockType = BlockType::tryFrom($blockType);
    }
}