<?php

declare(strict_types=1);

class AdminMainHeaderFactory
{
    public function __construct(private readonly IconBuilder $iconBuilder)
    {
    }

    public function create(HtmlBuilder $builder): AdminMainHeader
    {
        return new AdminMainHeader(
            $builder,
            new ButtonBuilder($builder, $this->iconBuilder),
            new Breadcrumbs($builder),
        );
    }
}