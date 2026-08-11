<?php

declare(strict_types=1);

final class UserCartComponent implements StandAloneComponentInterface
{
    public function __construct(
        private readonly IconBuilder $iconBuilder,
        private readonly HtmlBuilder $htmlBuilder,
        private readonly string $cartPageUrl = '/shopping-cart/index',
    ) {
    }

    #[Override]
    public function build(mixed $cartData = null): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;

        $count = $this->extractCount($cartData);

        $itemCountComponent = $html->tag('span')
            ->class('menu__actions--cart-item-count')
            ->content((string) $count)
            ->when($count > 0, fn ($el) => $el->class('visible'));

        return $html->tag('a')
            ->href($this->cartPageUrl)
            ->class('menu__actions-link', 'menu__actions--cart')
            ->custom(['data-count' => $count])
            ->add(
                $this->iconBuilder->createIcon('icon-cart', 'Shopping Cart', ['cart-icon']),
                $itemCountComponent,
            );
    }

    private function extractCount(CartData|CartResponse|array $cartData): int
    {
        if ($cartData instanceof CartData) {
            return $cartData->totalCount ?? 0;
        }

        if ($cartData instanceof CartResponse) {
            return $cartData->getTotalCount() ?? 0;
        }

        if (is_array($cartData) && isset($cartData['total_count'])) {
            return (int) $cartData['total_count'];
        }

        return 0;
    }
}