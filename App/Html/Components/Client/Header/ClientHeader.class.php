<?php

declare(strict_types=1);

class ClientHeader extends AbstractHtml
{
    protected const string PROVIDER_KEY = 'header';

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

        ['header_top' => $headerTop,'header_bottom' => $headerBottom] = $this->buildLayout($html);

        $headerTopHtml = $html->tag('div')->class('container', 'menu')->add(...$headerTop)->generate();
        $headerBottomHtml = $html->tag('div')->class('container', 'category-nav')->add(...$headerBottom)->generate();
        return [$headerTopHtml, $headerBottomHtml];
    }

    public function buildLayout(HtmlBuilder $html): array
    {
        return $this->sectionManager->getSections();
    }

    protected function getProviderKey(): string
    {
        return self::PROVIDER_KEY;
    }
}