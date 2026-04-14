<?php

declare(strict_types=1);

class IndexPage extends AbstractHtml
{
    protected const string PROVIDER_KEY = 'index_page';

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

        $provider->registerSections($html, $this->sectionManager);
        ['hero_section' => $heroSection,'small_banner_section' => $smallBanner] = $this->buildLayout($html);
        return [
            $heroSection->generate(),
            $smallBanner->generate(),
        ];
    }

    /**
     * @param HtmlBuilder $html
     *
     * @return AbstractHtmlComponent[]
     */
    public function buildLayout(HtmlBuilder $html): array
    {
        return $this->sectionManager->getSections();
    }

    protected function getProviderKey(): string
    {
        return self::PROVIDER_KEY;
    }
}