<?php

declare(strict_types=1);

final class ModelUpdateResult
{
    public function __construct(
        public EntityManagerInterface $em,
        public null|int|array $lastUpdateId = null,
        public bool $skipped = true,
    ) {
    }
}