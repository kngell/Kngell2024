<?php

declare(strict_types=1);

class ConfirmDeletionSectionProvider extends AbstractSectionProvider
{
    public function __construct(IconBuilder $iconBuilder)
    {
        parent::__construct($iconBuilder);
    }

    public function registerSections(HtmlBuilder $html, ?HtmlSectionManagerInterface $manager = null): void
    {
        $sections = [
            new DeletionSummarySection($html, $this->iconBuilder),
            new DeletionOptionSection($html, $this->iconBuilder),
            new DeletionImpactSection($html, $this->iconBuilder),
            new DeletionCheckBoxSection($html, $this->iconBuilder),
        ];

        $registeredKeys = [];
        $this->register($manager, $sections, $registeredKeys);
    }
}