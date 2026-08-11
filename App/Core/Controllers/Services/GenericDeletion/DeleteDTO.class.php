<?php

declare(strict_types=1);

class DeleteDTO
{
    public function __construct(
        public readonly bool $confirmed,
        public readonly string $deleteOption,
        public readonly ?string $blockType = null,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $post = $request->getPost();

        return new self(
            confirmed: (bool) $post->get('confirm_delete', false),
            deleteOption: $post->get('delete_option', 'archive'),
            blockType: $post->get('block_type', null),
        );
    }
}