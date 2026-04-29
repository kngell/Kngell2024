<?php

declare(strict_types=1);

class AdminController extends Controller
{
    public function __construct(
        FormCreatorService $frm,
        private ProductShowModel $product,
    ) {
        $this->layout('admin');
        $this->frm = $frm;
    }

    public function index(): string
    {
        $this->pageTitle('Admin Dashboard');
        return $this->render('index');
    }

    public function input(): string
    {
        $this->pageTitle('input');
        return $this->render('input');
    }

    public function productAdd(): string
    {
        $this->pageTitle('Add Product');

        $decorator = $this->decorate(
            ProductFormDecorator::class,
            $this,
            [
                'action' => 'product-save/index',
            ],
        );
        return $this->render('product-add', $decorator->page());
    }

    public function productShow(?int $id = null): string|Response
    {
        if (null === $id) {
            return $this->redirectWithError('There\'s no product to show');
        }
        $product = $this->product->getProduct($id);
        return $this->render('product-show', ['product' => $product, 'id' => $id]);
    }

    public function editProfile(): string
    {
        $this->pageTitle('Profile');
        $profile = new UserProfileDecorator($this);
        return $this->render('edit-profile', $profile->page());
    }

    public function productList(): string
    {
        $this->pageTitle('Product List');
        $product = $this->decorate(ProductListDecorator::class, $this);
        return $this->render('/components/table-list', $product->page());
    }

    public function productEdit(#[Alias(['public_id', 'pdt_id'])]string $id): string|Response
    {
        $this->pageTitle('Edit Product');
        $action = 'product-save/index';

        list($values, $errors, $files) = $this->getFlashData($action);

        if (empty($values) && isset($id)) {
            $values = ctype_digit($id)
                ? $this->product->getProduct((int) $id)
                : $this->product->getByUuid($id);
        }

        $decorator = $this->decorate(ProductFormDecorator::class, $this, [
            'action' => $action,
            'formValues' => $values,
            'formErrors' => $errors,
            'files' => $files,
        ]);
        // dd($values);
        return $this->render('product-add', $decorator->page());
    }

    public function contact(): string
    {
        $this->pageTitle('Contact');
        return $this->render('contact');
    }
}