<?php

declare(strict_types=1);

class AdminController extends Controller
{
    public function __construct(
        ProductFormCreator $frm,
        private ProductShowModel $product,
        private SqlCompositeQueryBuilderInterface $query,
    ) {
        $this->layout('admin');
        $this->frm = $frm;
    }

    public function index(): string
    {
        $this->pageTitle('Admin Dashboard');
        return $this->render('index');
    }

    public function login(): string
    {
        $this->pageTitle('login');
        return $this->render('login');
    }

    public function productAdd(): string
    {
        $this->pageTitle('Add Product');
        /** @var TestProduct */
        $entity = App::diget(TestProduct::class);
        $entity->assign(['name' => 'Dj menguele', 'quantity' => 50, 'slug' => 'slug_test', 'price' => .2514565]);
        $this->query->getEntityManager()->setEntity($entity);
        $query = $this->query;
        $q1 = $query->insert($entity)->build();
        $q2 = $query->insert()->build();
        $q3 = $query->insert(['name' => 'Dj menguele', 'quantity' => 50, 'slug' => 'slug_test', 'price' => .2514565])->build();
        $q4 = $query->insert('name', 'Dj menguele', 'quantity', 50, 'slug', 'slug_test', 'price', .2514565)->build();

        $q5 = $query->insert()->into('test_product')->columns(['name', 'quantity', 'slug', 'price'])->values(['Dj menguele',  50,  'slug_test',  .2514565]);
        $q6 = $query->insert()->into('test_product')->columns('name', 'quantity', 'slug', 'price')->values('Dj menguele', 50, 'slug_test', .2514565);
        $q7 = $query->insert('name', 'quantity', 'slug', 'price')->into('test_product')->values('Dj menguele', 50, 'slug_test', .2514565);
        $q8 = $query->insert('name', 'quantity', 'slug', 'price')->values('Dj menguele', 50, 'slug_test', .2514565);
        $entity1 = $entity->assign(['name' => 'Dj menguele', 'quantity' => 50, 'slug' => 'slug_test', 'price' => .2514565]);
        $entity2 = $entity->assign(['name' => 'EK JT', 'quantity' => 80, 'slug' => 'slug_test2', 'price' => .14536]);
        $entity3 = $entity->assign(['name' => 'MMPP', 'quantity' => 1000, 'slug' => 'slug_gg', 'price' => 1452.789]);

        $q8 = $query->insert($entity1, $entity2, $entity3)->build();

        $product = new ProductAddPageDecorator($this, 'create-product/add');
        return $this->render('product-add', $product->page());
    }

    public function productShow(?int $id = null): string|Response
    {
        if (null === $id) {
            return $this->redirectWithError('There\'s no product to show');
        }
        $product = $this->product->getProduct($id);
        return $this->render('product-show', ['product' => $product]);
    }

    public function editProfile(): string
    {
        $this->pageTitle('Profile');
        $profile = new UserProfileDecorator($this);
        return $this->render('edit-profile', $profile->page());
    }

    public function test(): string
    {
        $this->setLayout('test');
        $this->pageTitle('Test');
        return $this->render('test');
    }

    public function productList(): string
    {
        $this->pageTitle('Product List');
        return $this->render('product-list');
    }

    public function productEdit(): string
    {
        $this->pageTitle('Edit Product');
        return $this->render('product-edit');
    }

    public function contact(): string
    {
        $this->pageTitle('Contact');
        return $this->render('contact');
    }
}