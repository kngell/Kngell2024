<?php

declare(strict_types=1);

#[Attribute(Attribute::TARGET_METHOD)]
class Bean
{
    public ?string $name;

    /**
     * @param string|null $name
     */
    public function __construct(string|null $name = null)
    {
        $this->name = $name;
    }
}