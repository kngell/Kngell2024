<?php

declare(strict_types=1);

class AdminController extends Controller
{
    public function __construct(
        ProductFormCreator $frm,
        private ProductShowModel $product,
        private PaginationCachingFactory $paginFactory,
    ) {
        $this->layout('admin');
        $this->frm = $frm;
    }

    public function index(): string
    {
        $this->pageTitle('Admin Dashboard');
        return $this->render('index');
    }

    public function productAdd(): string
    {
        $this->pageTitle('Add Product');
        $decorator = new ProductFormDecorator(
            controller: $this,
            action:'product-operations/save',
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
        $product = new ProductListDecorator($this, $this->paginFactory, $this->product, new PaginationStateService());
        return $this->render('product-list', $product->page());
    }

    public function productEdit(#[Alias(['public_id', 'product_id'])]string $id): string|Response
    {
        $this->pageTitle('Edit Product');

        $decorator = new ProductFormDecorator(
            controller: $this,
            action:'product-operations/save',
            formValues: $this->product->getByUuid($id),
        );
        return $this->render('product-add', $decorator->page());
    }

    public function contact(): string
    {
        $this->pageTitle('Contact');
        return $this->render('contact');
    }
}