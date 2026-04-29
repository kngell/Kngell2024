<?php

declare(strict_types=1);

class IndexPage extends AbstractHtml
{
    protected const string PROVIDER_KEY = 'index_page';

    public function __construct(
        private readonly IndexSectionProvider $provider,
        private readonly HtmlRegularSectionManager $sectionManager,
        private HtmlBuilder $builder,
    ) {
    }

    public function getHtmlElements(): array
    {
        $this->provider->registerSections($this->builder, $this->sectionManager);
        return $this->buildLayout($this->builder);
    }

    /**
     * @param HtmlBuilder $html
     *
     * @return AbstractHtmlComponent[]
     */
    public function buildLayout(?HtmlBuilder $html = null): array
    {
        $sections = $this->sectionManager->getSections();
        $allSections = [];
        foreach (IndexPageSection::cases() as $case) {
            if (isset($sections[$case->value])) {
                $allSections[$case->value] = $sections[$case->value]->generate();
            }
        }

        return $allSections;
    }

    protected function getProviderKey(): string
    {
        return self::PROVIDER_KEY;
    }
}