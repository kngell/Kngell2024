<?php

declare(strict_types=1);

class CartEmptyComponent implements StandAloneComponentInterface
{
    public function __construct(
        private readonly HtmlBuilder $htmlBuilder,
        private readonly IconBuilder $iconBuilder,
    ) {
    }

    public function build(mixed $data = null): ?AbstractHtmlComponent
    {
        $html = $this->htmlBuilder;
        return $html->div()
            ->class('shopping-cart__empty')
            ->style([
                'display' => 'flex',
                'flex-direction' => 'column',
                'align-items' => 'center',
                'justify-content' => 'center',
                'width' => '100%',
                'padding' => '80px 20px',
                'text-align' => 'center',
                'min-height' => '500px',
            ])
            ->add(
                $html->div()
                    ->class('empty-cart-content')
                    ->style([
                        'max-width' => '500px',
                        'width' => '100%',
                    ])
                    ->add(
                        $this->iconBuilder->createIcon('icon-cart-empty', 'Empty cart', ['empty-cart-icon'])
                            ->style([
                                'width' => '120px',
                                'height' => '120px',
                                'margin' => '0 auto 30px',
                                'display' => 'block',
                                'opacity' => '0.5',
                                'color' => '#a0aec0',
                            ]),
                        $html->tag('h2')
                            ->style([
                                'font-size' => '32px',
                                'font-weight' => '600',
                                'margin-bottom' => '16px',
                                'color' => '#2d3748',
                            ])
                            ->content('Your cart is empty'),
                        $html->tag('p')
                            ->style([
                                'font-size' => '18px',
                                'color' => '#718096',
                                'margin-bottom' => '32px',
                                'line-height' => '1.6',
                            ])
                            ->content('Looks like you haven\'t added any items to your cart yet.'),
                        $html->tag('a')
                            ->href('/shop')
                            ->class('btn btn-primary btn-lg')
                            ->style([
                                'display' => 'inline-block',
                                'padding' => '14px 40px',
                                'background-color' => '#3182ce',
                                'color' => '#ffffff',
                                'border-radius' => '8px',
                                'text-decoration' => 'none',
                                'font-weight' => '600',
                                'font-size' => '16px',
                                'transition' => 'background-color 0.2s',
                                'border' => 'none',
                                'cursor' => 'pointer',
                            ])
                            ->content('Start Shopping'),
                    ),
            );
    }
}