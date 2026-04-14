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

        // Handle single insert
        if (!ArrayUtils::isArrayList($insertdata) && !ArrayUtils::isObjectList($insertdata) && ArrayUtils::isAssoc($insertdata)) {
            $singleData = isset($insertData[0]) ? $insertData[0] : $insertData;
            $entity = $this->em->getEntity();
            return $this->getSingleDataSetRule($singleData, 0, $entity);
        }

        // Handle batch insert
        $ruleSet = [];
        $entities = $this->em->getEntity();
        foreach ($insertData as $index => $dataSet) {
            $entity = $entities instanceof Entity ? $entities : $entities[$index];
            $ruleSet[] = $this->getSingleDataSetRule($dataSet, $index, $entity);
        }
        return implode(', ', $ruleSet);
    }

    protected function normalize(array|null $arrayInput): array
    {
        if (empty($arrayInput) || ArrayUtils::isObjectList($arrayInput)) {
            $arrayInput = !empty($arrayInput) ? $arrayInput : $this->em->getEntity();

            if ($arrayInput  instanceof Entity) {
                $data = $this->em->getEntityProperties();
                return [$data];
            }

            $insertData = [];
            /** @var Entity $singleEntity */
            foreach ($arrayInput as $singleEntity) {
                $data = $singleEntity->toArray();
                $insertData[] = $data;
            }
            return $insertData;
        }
        return $this->normalizeArrayInput($arrayInput);
    }

    private function normalizeArrayInput(array $arrayInput): array
    {
        if (count($arrayInput) === 1 && isset($arrayInput[0])) {
            $arrayInput = $arrayInput[0];
        }
        if ($arrayInput instanceof Entity) {
            return [$arrayInput->toArray()];
        }

        // Handle batch array data
        if (ArrayUtils::isArrayList($arrayInput)) {
            return $arrayInput;
        }

        return [$arrayInput];
    }

    private function getSingleDataSetRule(array $insertData, int $batchIndex, Entity $entity): string
    {
        $parameterRule = [];
        $helper = $this->createTableHelper();

        foreach ($insertData as $field => $value) {
            $paramField = $batchIndex > 0 ? "{$field}_{$batchIndex}" : $field;
            $parameterName = $this->createParameter($value, $paramField, $helper, null, $entity);
            $parameterRule[] = $parameterName;
        }
        return '(' . implode(', ', $parameterRule) . ')';
    }
}
