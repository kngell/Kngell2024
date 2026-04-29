<?php

declare(strict_types=1);

class AdminNavbar extends AbstractHtml
{
    protected const string PROVIDER_KEY = 'admin_navbar';

    public function __construct(
        private readonly SectionProviderFactory $providerFactory,
        private readonly HtmlRegularSectionManager $sectionManager,
        private HtmlBuilder $builder,
    ) {
    }

    public function getHtmlElements(): array
    {
        $html = $this->builder;
        $provider = $this->providerFactory->getProvider($this->getProviderKey());
        $provider->registerSections($this->builder, $this->sectionManager);
        ['admin_nav_section' => $navbar] = $this->buildLayout($html);
        return [$navbar->generate()];
    }

    public function buildLayout(?HtmlBuilder $html = null): array
    {
        return $this->sectionManager->getSections();
    }

    protected function getProviderKey(): string
    {
        return self::PROVIDER_KEY;
    }
}