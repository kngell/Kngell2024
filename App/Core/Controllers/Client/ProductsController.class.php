<?php

declare(strict_types=1);
class ProductsController extends Controller
{
    private ProductModel $product;

    public function __construct(View $view, ProductModel $product)
    {
        parent::__construct($view);
        $this->product = $product;
    }

    public function index() : string
    {
        $query = $this->product->getSql();
        $update = $this->product->refresh();
        $insert = $this->product->create();
        $delete = $this->product->del();
        $products = $this->product->findAll();
        $this->pageTitle('Products');
        return $this->render('products/index', [
            'products' => $products,
            'query' => [$query->getQuery(), 'parameters' => $query->getParameters(), 'bindArr' => $query->getBindArr()],
            'update' => [$update->getQuery(), 'parameters' => $update->getParameters()],
            'insert' => [$insert->getQuery()],
            'delete' => [$delete->getQuery(), 'parameters' => $delete->getParameters()],
        ]);
    }

    public function show(string $id) : string
    {
        $this->pageTitle('Show');
        $product = $this->product->find($id);
        if ($product === false) {
            throw new PageNotFoundException("Product $id not found");
        }
        return $this->render('show', ['product' => $product]);
    }

    public function showPage(string $title, string $id, string $page)
    {
        dump($title, $id, $page);

        return $title;
    }
}