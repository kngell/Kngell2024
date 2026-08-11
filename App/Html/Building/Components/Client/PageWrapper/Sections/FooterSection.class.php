<?php

declare(strict_types=1);

class FooterSection extends AbstractBaseHtmlSection
{
    public function __construct(
        HtmlBuilder $htmlBuilder,
        IconBuilder $iconBuilder,
        private readonly FooterService $footerService,
        private readonly SocialLinksService $socialLinksService,
        private readonly ?string $currentYear = null,
        private ?string $page = null,
    ) {
        parent::__construct($htmlBuilder, $iconBuilder);
    }

    public function getKey(): string
    {
        return PageWrapperSection::FOOTER->value;
    }

    public function getConfig(array $formValues = []): array|AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $footerResponses = $this->footerService->getForpage($this->page);

        $year = $this->currentYear ?? date('Y');
        return $html->tag('div')->class('container', 'footer__container')->add(
            $this->renderNavigationInfos($footerResponses),
            $this->renderSocialSection($year),
        );
    }

    private function renderNavigationInfos(array $responses): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $aboutText = $about['text'] ?? 'We are a residential interior design firm located in Portland. Our boutique-studio offers more than';

        return $html->tag('div')->class('footer__container--info')->add(
            $html->tag('div')->class('about')->add(
                $html->tag('a')
                    ->href('/')
                    ->class('logo-container', 'about__logo')
                    ->add(
                        $this->iconBuilder->createIcon('icon-logo', 'Logo', ['logo']),
                    ),
                $html->tag('p')->class('about__text')->content($aboutText),
            ),
            $this->renderNavigation($responses ?? $this->getDefaultNavigationColumns()),
        );
    }

    private function renderNavigation(array $responses): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $nav = $html->tag('nav')->class('footer-navigation');

        foreach ($responses as $response) {
            $nav->add($this->renderNavigationColumn($response));
        }

        return $nav;
    }

    private function renderNavigationColumn(FooterResponse $response): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $title = $response->getTitle();
        $items = $response->getMenuItems() ?? [];

        $list = $html->tag('ul')->class('navigation-list');

        foreach ($items as $item) {
            $link = $html->tag('a')
                ->href($item['url'] ?? '#')
                ->class('navigation-list__item--link')
                ->content($item['title'] ?? '');

            $list->add(
                $html->tag('li')->class('navigation-list__item')->add($link),
            );
        }

        return $html->tag('div')->class('footer-navigation__container')->add(
            $html->tag('h5')->class('navigation-title')->content($title),
            $list,
        );
    }

    private function renderSocialSection(string $year): AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        $socialLinks = $this->socialLinksService->getActiveLinks();
        $socialContainer = $html->tag('div')->class('footer__container--socials');

        foreach ($socialLinks as $link) {
            $socialContainer->add(
                $html->tag('a')
                    ->href($link['url'])
                    ->class('social-container')
                    ->attribute('target', '_blank')
                    ->attribute('rel', 'noopener noreferrer')
                    ->attribute('aria-label', $link['name'])
                    ->add(
                        $this->iconBuilder->createIcon($link['icon'], $link['name'], $link['icon_class']),
                    ),
            );
        }

        // Add copyright
        $socialContainer->add(
            $html->tag('div')->class('copyright')->content("© {$year} Your Company. All rights reserved."),
        );

        return $socialContainer;
    }

    private function getDefaultNavigationColumns(): array
    {
        return [
            [
                'title' => 'Services',
                'items' => [
                    ['title' => 'Bonus program', 'url' => '/bonus'],
                    ['title' => 'Gift cards', 'url' => '/gift-cards'],
                    ['title' => 'Credit and payment', 'url' => '/payment'],
                    ['title' => 'Service contracts', 'url' => '/contracts'],
                    ['title' => 'Non-cash account', 'url' => '/non-cash'],
                    ['title' => 'Payment', 'url' => '/payment'],
                ],
            ],
            [
                'title' => 'Assistance to the buyer',
                'items' => [
                    ['title' => 'Find an order', 'url' => '/order-status'],
                    ['title' => 'Terms of delivery', 'url' => '/delivery'],
                    ['title' => 'Exchange and return of goods', 'url' => '/returns'],
                    ['title' => 'Guarantee', 'url' => '/guarantee'],
                    ['title' => 'Frequently asked questions', 'url' => '/faq'],
                    ['title' => 'Terms of use of the site', 'url' => '/terms'],
                ],
            ],
        ];
    }

    private function asset(string $path): string
    {
        return HOST . '/public/' . ltrim($path, '/');
    }
}