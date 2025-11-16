<?php

declare(strict_types=1);

class InsertRules extends AbstractRules implements QueryRulesInterface
{
    public function __construct(
        EntityManagerInterface $em,
        string $method,
        QueryState $state,
        private array $insertdata,
    ) {
        parent::__construct($em, $method, $state);
    }

    public function getRule(array $insertdata): string
    {
        $insertData = $this->normalize($insertdata);
        if (!ArrayUtils::isMultidimentional($insertData)) {
            return $this->getSingleDataSetRule(...$insertData);
        }
        $ruleSet = [];
        foreach ($insertData as $dataSet) {
            $ruleSet[] = $this->getSingleDataSetRule(...$dataSet);
        }
        return implode('; ', $ruleSet);
    }

    protected function normalize(array|null $arrayInput): array
    {
        if (empty($arrayInput)) {
            $entity = $this->em->getEntity();

            if ($entity instanceof Entity) {
                $data = $this->em->getEntityProperties();
                return [$data, $entity];
                // return $this->normalizer->normalizeValuesForDatabase($values, $entity);
            }
            if ($entity instanceof CollectionInterface) {
                $insertData = [];
                /** @var Entity $singleEntity */
                foreach ($entity as $singleEntity) {
                    $data = $singleEntity->toArray();
                    $insertData[] = [$data, $singleEntity];

                    // $insertData[] = $this->normalizer->normalizeValuesForDatabase($values, $singleEntity);
                    return $insertData;
                }
            }
        }

        return $arrayInput;
    }

    private function getSingleDataSetRule(array $insertData, Entity $entity, TablesAliasHelper $helper): string
    {
        $parameterRule = [];

        foreach ($insertData as $field => $value) {
            $parameterName = $this->createParameter($value, $field, $helper, null, $entity);
            $parameterRule[] = ':' . $parameterName;
        }
        return implode(', ', $parameterRule);
    }
}