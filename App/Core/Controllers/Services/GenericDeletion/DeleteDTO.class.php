<?php

declare(strict_types=1);

class DeleteDTO
{
    public function __construct(
        public readonly bool $confirmed,
        public readonly string $deleteOption,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $post = $request->getPost();

        return new self(
            confirmed: (bool) $post->get('confirmed', false),
            deleteOption: $post->get('delete_option', 'archive'),
        );
    }
}