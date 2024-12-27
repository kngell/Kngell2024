<?php

declare(strict_types=1);

class ProductModel extends Model
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    public function getTotal() : int
    {
        $this->entityManager->createQueryBuilder()->select('count(name) AS tot')->build();
        $total = $this->entityManager->persist()->getResults();
        $count = ArrayUtils::first($total->getResults()->all());
        return $count['tot'];
    }
}