<?php

declare(strict_types=1);

class ProductsHtmlElement extends AbstractHtml
{
    /** @var object[] */
    private array $products;
    private array $wrapperClass = ['card'];
    private array $wrapperStyle = ['width: 18rem;'];

    private HtmlBuilder $builder;

    /**
     * @param array $products
     * @param HtmlBuilder $builder
     * @return void
     */
    public function __construct(array $products, HtmlBuilder $builder)
    {
        $this->products = $products;
        $this->builder = $builder;
    }

    public function display(): string
    {
        $html = $this->builder;
        $productsHtml = [];
        /** @var object $product */
        foreach ($this->products as $product) {
            $form = $html->form();
            $productsHtml[] = $html->tag('div')->class(...$this->wrapperClass)->style($this->wrapperStyle)->add(
                $html->tag('div')->class('card-body')->add(
                    $html->tag('img')->src($this->media($product->media))->class('card-img-top')->alt('...'),
                    $html->tag('div')->class('card-body')->add(
                        $html->tag('h5')->class('card-title text-primary')->content($product->name . ' by ' . $product->brand_name)->add(
                            $html->tag('strong')->content(' $' . $product->price)
                        ),
                        $html->tag('p')->class('card-text')->content($product->description),
                        $form->action('cart/add-to-cart')->method('POST')->add(
                            $form->input('hidden')->name('product_id')->value($product->id),
                            $form->button()->type('submit')->class('btn btn-warning')->content('Add to Cart')
                        )
                    )
                )
            )->generate();
        }
        return implode(' ', $productsHtml);
    }
}