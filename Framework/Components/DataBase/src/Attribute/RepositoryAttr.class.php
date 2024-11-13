<?php

declare(strict_types=1);

#[Attribute(Attribute::TARGET_CLASS)]
readonly class RepositoryAttr extends Service
{
    public function __construct(string|null $name = null)
    {
        parent::__construct($name);
    }
}