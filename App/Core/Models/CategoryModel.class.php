<?php

declare(strict_types=1);

class CategoryModel extends Model
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function getId()
    {
        $repository = $this->entityManager->getRepository('Category');
        return $repository->create();
    }
}