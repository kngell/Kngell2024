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
            'product_summary' => new ProductSummarySection($html, $this->iconBuilder),
            'deletion_option' => new DeletionOptionSection($html, $this->iconBuilder),
            'deletion_impact' => new DeletionImpactSection($html, $this->iconBuilder),
            'confirm_deletion_checkbox' => new DeletionCheckBoxSection($html, $this->iconBuilder),
        ];

        $registeredKeys = [];
        $this->register($manager, $sections, $registeredKeys);
    }
}