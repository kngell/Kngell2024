<?php

declare(strict_types=1);
interface SingleEntityServiceInterface
{
    public function getForPage(?string $page = null): EntityResponseInterface;

    public function getDefaultResponse(): EntityResponseInterface;
}