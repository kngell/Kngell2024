<?php

declare(strict_types=1);
class ProductsController extends Controller
{
    private ProductModel $product;

    public function __construct(ProductModel $product)
    {
        $this->product = $product;
    }

    public function index() : string
    {
        $products = $this->product->all();
        $total = $this->product->getTotal();
        $this->pageTitle('Products');
        return $this->render('products/index', [
            'products' => $products->getResults(),
            'total' => $total,

        ]);
    }

    public function show(string $id) : string
    {
        $this->pageTitle('Show');
        $product = $this->product->find($id)->getResults();
        if ($product === false) {
            throw new PageNotFoundException("Product $id not found");
        }
        return $this->render('show', ['product' => $product]);
    }

    public function edit(string $id, string|null $form = null) : string
    {
        $this->pageTitle('Edit Product');
        $product = $this->product->find($id)->getResults();
        if ($form === null) {
            $form = $this->makeForm("products/$id/update", $product);
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
        $form = $form ?? $this->makeForm('products/create');
        return $this->render('new', ['insertFrom' => $form]);
    }

    public function update(string $id) : string
    {
        $data = $this->request->getPost()->getAll();
        $validator = new Validator($data, 'productFormRules');
        $errors = $validator->validate();
        if (! empty($errors)) {
            $form = $this->makeForm("products/update/{$id}", $data, $errors);
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
        $results = $validator->validate();
        if (! empty($results)) {
            $form = $this->makeForm('products/create', [], $results);
            if (! $this->session->exists('form')) {
                $this->session->set('form', $form);
                $r = $_SESSION;
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
        $product = $this->product->find($id)->getResults();
        $form = $this->deleteFormConfirmation("products/destroy/{$product['id']}", $product);
        return $this->render('delete', ['product' => $product, 'deleteForm' => $form]);
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

    protected function makeForm(string $action = '', array $formValues = [], array $formErrors = []) : string
    {
        $form = $this->formBuilder->form();
        return
        $form->name('new-product')->method('post')->class(['mb-3'])->action($action)->enctype(true)->formValues($formValues)->formErrors($formErrors)->add(
            ! empty($formValues) ? $form->input('hidden')->name('id')->value('') : null,
            $form->label()->for('pdt')->content('Name :')->class(['form-label']),
            $form->input('text')->name('name')->value('')->id('pdt')->class(['form-control']),
            $form->label()->for('description')->content('Description :')->class(['form-label']),
            $form->textArea()->name('description')->id('description')->class(['form-control']),
            $form->button()->content('save')->type(ButtontypeAttr::SUBMIT->value)->class(['button', 'btn',
                'btn-primary'])->name('button')
        )->makeForm();
    }

    private function deleteFormConfirmation(string $action = '', array $formValues = [], array $formErrors = []) : string
    {
        $form = $this->formBuilder->form();
        return $form->name('confirm-delete-product')->method('post')->action($action)->add(
            $form->htmlTag('p')->class(['text-center'])->content('Are you sur you want to delete this product?'),
            $form->button()->name('submit')->content('Yes')->class(['btn', 'btn-info'])
        )->makeForm();
    }
}
