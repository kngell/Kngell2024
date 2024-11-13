<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

#[RepositoryAttr]
class ProductRepository extends EntityRepository
{
    public function __construct(EntityManager $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata(Product::class));
    }

    public function getProductByName(string $name) : Product|null
    {
        $sql = 'SELECT p from Product p WHERE p.name = ?1';

        return $this->getEntityManager()->createQuery($sql)
            ->setParameter(1, $name)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}