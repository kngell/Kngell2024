<?php

declare(strict_types=1);

class AdminController extends Controller
{
    public function __construct(
        FormCreatorService $frm,
        private ProductShowModel $product,
        private readonly ProductTableConfigFactory $tableFactory,
        private readonly ProductFormConfigFactory $formFactory,
    ) {
        $this->layout(NavbarType::ADMIN);
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
            FormDecorator::class,
            $this,
            [
                'action' => '/admin/product-save/index',
                'factory' => $this->formFactory,
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
        $list = $this->decorate(
            ListDecorator::class,
            $this,
            [
                'factory' => $this->tableFactory,
                'adapter' => new ProductPaginatedAdapter($this->product),
            ],
        );
        return $this->render('/table-list', $list->page());
    }

    public function productEdit(#[Alias(['public_id', 'pdt_id'])]string $id): string|Response
    {
        $this->pageTitle('Edit Product');

        // $logger = new CustomLogger(
        //     STORAGE . 'logs' . DS . 'app.log',
        //     true,  // displayInBrowser
        //     3,     // debugLevel
        //     true,   // enabled
        // );

        // // Test messages
        // $logger->info('Test info message');
        // $logger->warning('Test warning message');
        // $logger->error('Test error message');

        $action = '/admin/product-save/index';

        list($values, $errors, $files) = $this->getFlashData($action);

        if (empty($values) && isset($id)) {
            $values = ctype_digit($id)
                ? $this->product->getProduct((int) $id)
                : $this->product->getByUuid($id);
        }

        if (empty($values)) {
            $this->flash->add('Product no longer available', FlashType::WARNING);
            return $this->redirect('/admin/admin/product-list');
        }

        $decorator = $this->decorate(FormDecorator::class, $this, [
            'action' => $action,
            'formValues' => $values,
            'formErrors' => $errors,
            'files' => $files,
            'factory' => $this->formFactory,
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