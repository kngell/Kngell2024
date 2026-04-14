<?php

declare(strict_types=1);

class IndexPageDecorator extends AbstractHtmlDecorator
{
    public function page(): array
    {
        $target = $this->getTarget();
        if (!$target instanceof EcommerceController) {
            throw new HtmlDecoratorException(
                sprintf(
                    '%s requires EcommerceController, got %s',
                    self::class,
                    get_class($target),
                ),
            );
        }
        $indexPage = new IndexPage(
            $target->getProviderFactory(),
            $target->getSectionManager(),
            $target->getBuilder(),
        );
        [$heroSection,$smallBanner] = $indexPage->getHtmlElements();
        return parent::page() + [
            'heroSection' => $heroSection,
            'smallBannerSection' => $smallBanner,
        ];
    }
}