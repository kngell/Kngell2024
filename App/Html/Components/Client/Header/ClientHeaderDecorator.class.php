<?php

declare(strict_types=1);

class ClientHeaderDecorator extends AbstractHtmlDecorator
{
    public function __construct(
        private readonly HeaderSectionProvider $provider,
    ) {
    }

    public function page(): array
    {
        if (!$this->controller instanceof EcommerceController) {
            throw new HtmlDecoratorException(get_class($this->controller) . ' is not a valid instance of Controller');
        }
        $header = new ClientHeader(
            $this->provider,
            $this->controller->getSectionManager(),
            $this->controller->getBuilder(),
        );
        [$headerTop,$headerBottom] = $header->getHtmlElements();
        return parent::page() + [
            'headerTop' => $headerTop,
            'headerBottom' => $headerBottom,
        ];
    }
}