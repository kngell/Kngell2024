<?php

declare(strict_types=1);

class OffsetRule extends AbstractRules implements QueryRulesInterface
{
    public function __construct(
        private array $OffsetMap,
        EntityManagerInterface $em,
        ?string $method,
        QueryState $state,
        ?string $customAlias = null,
    ) {
        parent::__construct($em, $method, $customAlias, $state);
    }

    public function getRule(array $conditions): string
    {
        $normalized = $this->normalize($conditions);

        if (empty($normalized)) {
            return '';
        }

        $offsetValue = $normalized[0];
        $parameterName = $this->createOffsetParameter($offsetValue);

        return "{$parameterName}";
    }

    protected function normalize(array $arrayInput): array
    {
        // Input format: ['offset' => 20, 'method' => 'offset']
        // Or: [20, 'method' => 'offset']

        // Remove method key if present
        if (isset($arrayInput['method'])) {
            unset($arrayInput['method']);
        }

        // Extract offset value
        if (isset($arrayInput['offset'])) {
            $value = $arrayInput['offset'];
        } elseif (!empty($arrayInput) && is_numeric($arrayInput[0] ?? null)) {
            $value = $arrayInput[0];
        } else {
            return []; // No offset specified
        }

        // Validate (offset can be 0)
        if (!is_numeric($value) || $value < 0) {
            throw new InvalidArgumentException('OFFSET must be a non-negative integer');
        }

        return [(int) $value];
    }

    private function createOffsetParameter(int $offsetValue): string
    {
        $tableHelper = $this->em->getTableAliasHelper();
        $entity = $this->em->getEntity();

        return $this->createParameter(
            $offsetValue,
            'offset',
            $tableHelper,
            null, // No index needed for offset
            $entity,
        );
    }
}