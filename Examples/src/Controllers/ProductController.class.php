<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManager;

#[ControllerAttr]
class ProductController
{
    public function __construct(
        private EntityManager $em,
        private ProductRepository $productRepo,
        private TwigView $view
    ) {
    }

    #[PostPathAttr(path: '/products/{name}')]
    #[ResponseBody(type: ResponseBodyType::JSON)]
    #[ResponseStatus(statusCode: HttpStatusCode::HTTP_CREATED)]
    public function createProduct(#[PathVariableAttr] string $name) : Product
    {
        $product = new Product();
        $product->setName($name);
        $this->em->persist($product);
        $this->em->flush();

        return $product;
    }

    #[GetPathAttr(path: '/products/{name}')]
    #[ResponseBody(type: ResponseBodyType::RAW, produces: 'text/html')]
    #[ResponseStatus(statusCode: HttpStatusCode::HTTP_OK)]
    public function getProduct(#[PathVariableAttr] string $name) : string
    {
        $product = $this->productRepo->getProductByName($name);
        return  $this->view->render('product.html.twig', ['product' => $product]);
    }
}