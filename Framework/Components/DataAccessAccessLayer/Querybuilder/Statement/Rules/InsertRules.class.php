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
        if (!isset($insertData[0]) || !is_array($insertData[0]) || !ArrayUtils::isMultidimentional($insertData)) {
            $singleData = isset($insertData[0]) ? $insertData[0] : $insertData;
            return $this->getSingleDataSetRule($singleData);
        }

        // Handle batch insert
        $ruleSet = [];
        foreach ($insertData as $index => $dataSet) {
            $ruleSet[] = $this->getSingleDataSetRule($dataSet, $index);
        }
        return implode(', ', $ruleSet);
    }

    protected function normalize(array|null $arrayInput): array
    {
        if (empty($arrayInput) || ArrayUtils::isObjectList($arrayInput)) {
            $entity = $arrayInput ? $arrayInput : $this->em->getEntity();

            if ($entity instanceof Entity) {
                $data = $this->em->getEntityProperties();
                return [$data];
            }
            $insertData = [];
            /** @var Entity $singleEntity */
            foreach ($entity as $singleEntity) {
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
        if (ArrayUtils::isMultidimentional($arrayInput) && ArrayUtils::isSequential($arrayInput)) {
            return $arrayInput;
        }

        return [$arrayInput];
    }

    private function getSingleDataSetRule(array $insertData, int $batchIndex = 0): string
    {
        $parameterRule = [];
        $helper = $this->em->getTableAliasHelper();
        $entity = $this->em->getEntity();

        foreach ($insertData as $field => $value) {
            // Add batch index to parameter names for batch inserts
            $paramField = $batchIndex > 0 ? "{$field}_{$batchIndex}" : $field;
            $parameterName = $this->createParameter($value, $paramField, $helper, null, $entity);
            $parameterRule[] = ':' . $parameterName;
        }
        return '(' . implode(', ', $parameterRule) . ')';
    }
}