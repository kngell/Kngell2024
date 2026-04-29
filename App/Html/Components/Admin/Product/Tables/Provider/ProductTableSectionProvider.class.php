<?php

declare(strict_types=1);

final class ProductTableSectionProvider extends AbstractSectionProvider
{
    public function __construct(
        private HtmlBuilder $builder,
        IconBuilder $icon,
        private HtmlSectionPresentationService $presenter,
    ) {
        parent::__construct($icon);
    }

    public function registerSections(HtmlBuilder $html, ?HtmlSectionManagerInterface $manager = null): void
    {
        $tableSections = [
            new ProductTableCaptionSection(
                $this->builder,
                $this->iconBuilder,
                $this->presenter,
            ),
            new ProductTableColGroupSection(
                $this->builder,
                $this->iconBuilder,
                $this->presenter,
            ),
            new ProductTableBodySection(
                $this->builder,
                $this->iconBuilder,
                $this->presenter,
            ),
            new ProductTableHeadSection(
                $this->builder,
                $this->iconBuilder,
                $this->presenter,
            ),
        ];
        $registeredKeys = [];
        $this->register($manager, $tableSections, $registeredKeys);
    }
}