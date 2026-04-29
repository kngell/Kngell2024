<?php

declare(strict_types=1);

class ClientHeader extends AbstractHtml
{
    protected const string PROVIDER_KEY = 'header';

    public function __construct(
        private readonly HeaderSectionProvider $provider,
        private readonly HtmlRegularSectionManager $sectionManager,
        private HtmlBuilder $builder,
    ) {
    }

    public function getHtmlElements(): array
    {
        $html = $this->builder;
        $this->provider->registerSections($html, $this->sectionManager);

        ['header_top' => $headerTop,'header_bottom' => $headerBottom] = $this->buildLayout($html);

        $headerTopHtml = $html->tag('div')->class('container', 'menu')->add(...$headerTop)->generate();
        $headerBottomHtml = $html->tag('div')->class('container', 'category-nav')->add(...$headerBottom)->generate();
        return [$headerTopHtml, $headerBottomHtml];
    }

    /**
     * @return array{
     *     header_top: AbstractHtmlComponent[],
     *     header_bottom: AbstractHtmlComponent[]
     * }
     */
    public function buildLayout(?HtmlBuilder $html = null): array
    {
        return $this->sectionManager->getSections();
    }

    protected function getProviderKey(): string
    {
        return self::PROVIDER_KEY;
    }
}