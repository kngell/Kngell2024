<?php

declare(strict_types=1);

class ProductRegionalPriceModel extends Model
{
    public function save(null|array|Entity $data = null, array $conditions = []): QueryResult
    {
        // $priceEntity = $this->findOrCreate($conditions);

        // if ($priceEntity->getEntityPrimarykeyValue()) {
        //     $priceEntity->track();
        // }
        // $priceEntity->assign($data);
        // $this->addToIdentityMap($priceEntity);
        return parent::save($data);
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
