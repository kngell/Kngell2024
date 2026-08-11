<?php

declare(strict_types=1);
interface CollectionEntityServiceInterface
{
    /**
     * @return array<EntityResponseInterface>
     */
    public function getForPage(?string $page = null): array;

    /**
     * @return array<EntityResponseInterface>
     */
    public function getDefaultResponse(): array;
}