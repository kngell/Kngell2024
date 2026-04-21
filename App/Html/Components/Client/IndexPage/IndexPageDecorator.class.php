<?php

declare(strict_types=1);

class IndexPageDecorator extends AbstractHtmlDecorator
{
    public function __construct(
        private readonly IndexSectionProvider $provider,
    ) {
    }

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
            $this->provider,
            $target->getSectionManager(),
            $target->getBuilder(),
        );

        return parent::page() + $indexPage->getHtmlElements();
    }
}