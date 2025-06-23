<?php

declare(strict_types=1);

class ProductsController extends Controller
{
    private ProductModel $product;

    public function __construct(ProductModel $product, private ProductFormCreator $frm)
    {
        $this->product = $product;
    }

    public function index() : string
    {
        $this->pageTitle('Products');
        $products = new ProductsDecorator($this);
        $products = new UserCartItemDecorator($products);
        return $this->render('products/index', $products->page());
    }

    public function show(string $id) : string
    {
        $this->pageTitle('Show');
        $product = $this->product->find($id)->getResults()->single();
        if ($product === false) {
            throw new PageNotFoundException("Product $id not found");
        }
        return $this->render('show', ['product' => $product]);
    }

    public function edit(string $id, string|null $form = null) : string
    {
        $this->pageTitle('Edit Product');
        $product = $this->product->find($id)->getResults()->first();
        if ($form === null) {
            $form = $this->frm->make("products/$id/update", $product);
        }
        if ($product === false) {
            throw new PageNotFoundException("Product $id not found");
        }
        return $this->render('edit', ['product' => $product, 'editFrom' => $form]);
    }

    public function new() : string
    {
        if ($this->session->exists('form')) {
            $form = $this->session->get(('form'));
            $this->session->delete('form');
        }
        $form = $form ?? $this->frm->make('products/create');
        return $this->render('new', ['insertFrom' => $form]);
    }

    public function update(string $id) : Response
    {
        $data = $this->request->getPost()->getAll();
        $validator = new Validator($data, 'productFormRules');
        $errors = $validator->validate($data, 'products');
        if (! empty($errors)) {
            $form = $this->frm->make("products/update/{$id}", $data, $errors);
            return $this->edit($id, $form);
        }
        $update = $this->product->save($data);
        if ($update->getQueryResult()) {
            return $this->redirect("/products/{$id}/show");
        }
    }

    public function create() : Response|string
    {
        $data = $this->request->getPost()->getAll();
        $validator = new Validator($data, 'productFormRules');
        $results = $validator->validate($data, 'products');
        if (! empty($results)) {
            $form = $this->frm->make('products/create', [], $results);
            if (! $this->session->exists('form')) {
                $this->session->set('form', $form);
            }
            return $this->redirect('/products/new');
        }
        $insert = $this->product->save($data);
        if ($insert->getQueryResult()) {
            return $this->redirect("/products/{$insert->getLastInsertId()}/show");
        }
    }

    public function delete(string $id) : string
    {
        $product = $this->product->find($id)->getResults()->single();
        if ($product !== false) {
            $form = $this->frm->make("products/destroy/{$product['id']}", $product);
            return $this->render('delete', ['product' => $product, 'deleteForm' => $form]);
        }
        $this->redirect("products/edit/{$id}");
    }

    public function destroy(string $id) : Response
    {
        if ($this->request->getServer()->get('request_method') !== 'POST') {
            throw new InvalidPathException('You do not have permission to access this page');
        }
        $delete = $this->product->delete($id)->getResults();
        return $this->redirect('/products/index');
    }

    public function responseCodeExample() : Response
    {
        $this->response->setStatusCode(HttpStatusCode::HTTP_UNAVAILABLE_FOR_LEGAL_REASONS);
        $this->response->setContent('Unavailable for leagal reasons');
        return $this->response;
    }
}