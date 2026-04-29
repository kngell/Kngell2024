<?php

declare(strict_types=1);

class AdminMainHeaderFactory
{
    public function __construct(private readonly ButtonBuilder $buttonBuilder, private readonly IconBuilder $iconBuilder)
    {
    }

    public function create(HtmlBuilder $builder): AdminMainHeader
    {
        return new AdminMainHeader(
            $builder,
            $this->buttonBuilder,
            new Breadcrumbs($builder),
        );
    }

    public function createSubHeader(HtmlBuilder $builder): AdminSearchAndFilter
    {
        return new AdminSearchAndFilter(
            $builder,
            $this->iconBuilder,
        );
    }
}