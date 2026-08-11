<?php

declare(strict_types=1);

final class TableSectionProvider extends AbstractSectionProvider
{
    public function __construct(
        private readonly HtmlBuilder $builder,
        private readonly HtmlEscaper $escaper,
        private readonly TableConfig $config,
        IconBuilder $icon,
    ) {
        parent::__construct($icon);
    }

    public function registerSections(HtmlBuilder $html, ?HtmlSectionManagerInterface $manager = null): void
    {
        $tableSections = [
            new TableCaptionSection(
                $this->builder,
                $this->iconBuilder,
                $this->escaper,
                $this->config,
            ),
            new TableColGroupSection(
                $this->builder,
                $this->iconBuilder,
                $this->escaper,
                $this->config,
            ),
            new TableHeadSection(
                $this->builder,
                $this->iconBuilder,
                $this->escaper,
                $this->config,
            ),
            new TableBodySection(
                $this->builder,
                $this->iconBuilder,
                $this->escaper,
                $this->config,
            ),
        ];

        $registeredKeys = [];
        $this->register($manager, $tableSections, $registeredKeys);
    }
}