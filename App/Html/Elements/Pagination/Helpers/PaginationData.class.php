<?php

declare(strict_types=1);

class PaginationData
{
    public function __construct(
        public readonly int $currentPage,
        public readonly int $recordsPerPage,
        public readonly array $allowedPageSizes,
    ) {
    }
}