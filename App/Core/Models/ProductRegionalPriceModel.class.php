<?php

declare(strict_types=1);
class ProductRegionalPriceModel extends Model
{
    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct($em);
    }

    /**
     * @throws PDOException
     * @throws QueryResultException
     *
     * @return ProductRegionalPrice[]
     */
    public function getPrice(): array
    {
        $this->em->getRepository(ProductRegionalPrice::class)->findActiveSales('us');

        $rawResults = $this->em->persist()->getQueryResult()->setOperation('all')->asClass();
        $results = [];
        /** @var ProductRegionalPrice $entity */
        foreach ($rawResults as $entity) {
            // Call completeHydration to assign relationships, but keep the entity
            $entity->completeHydration();
            $results[] = $entity; // Add the entity itself to results
        }

        return $results;
    }
}