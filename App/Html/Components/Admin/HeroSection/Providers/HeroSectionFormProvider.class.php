<?php

declare(strict_types=1);

class HeroSectionFormProvider extends AbstractSectionProvider
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
            'basic-information' => new HeroBasicInformationSection($html, $this->iconBuilder, $this->header),
            'call-to-action' => new HeroCallToActionSection($html, $this->iconBuilder, $this->header, new ToggleSwitch($html)),
            'media' => new HeroMediaSection($html, $this->iconBuilder, $this->header),
        ];

        $registeredKeys = [];
        $this->register($manager, $sections, $registeredKeys);
    }
}