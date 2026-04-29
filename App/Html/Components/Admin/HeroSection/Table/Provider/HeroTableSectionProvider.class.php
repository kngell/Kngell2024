<?php

declare(strict_types=1);

final class HeroTableSectionProvider extends AbstractSectionProvider
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
            new HeroTableCaptionSection(
                $this->builder,
                $this->iconBuilder,
                $this->presenter,
            ),
            new HeroTableColGroupSection(
                $this->builder,
                $this->iconBuilder,
                $this->presenter,
            ),
            new HeroTableBodySection(
                $this->builder,
                $this->iconBuilder,
                $this->presenter,
            ),
            new HeroTableHeadSection(
                $this->builder,
                $this->iconBuilder,
                $this->presenter,
            ),
        ];
        $registeredKeys = [];
        $this->register($manager, $tableSections, $registeredKeys);
    }
}