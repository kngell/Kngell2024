<?php

declare(strict_types=1);

class SmallBannerSectionProvider extends AbstractSectionProvider
{
    public function __construct(
        IconBuilder $iconBuilder,
        private readonly FormSectionHeader $header,
    ) {
        parent::__construct($iconBuilder);
    }

    public function registerSections(HtmlBuilder $html, ?HtmlSectionManagerInterface $manager = null): void
    {
        $sections = [
            'core-configuration' => new CoreConfigurationSection($html, $this->iconBuilder, $this->header),
            'product-relationship' => new ProductRelationshipSection($html, $this->iconBuilder, $this->header),
            'custom-override' => new CustomContentOverrideSection($html, $this->iconBuilder, $this->header),
            'media' => new BannerMediaSection($html, $this->iconBuilder, $this->header),
            'display-settings' => new DisplaySettingsSection($html, $this->iconBuilder, $this->header, new FormOptions($html), new ToggleSwitch($html)),
        ];

        $registeredKeys = [];
        $this->register($manager, $sections, $registeredKeys);
    }
}