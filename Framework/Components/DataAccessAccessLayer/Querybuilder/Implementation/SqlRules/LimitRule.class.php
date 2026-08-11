<?php

declare(strict_types=1);

class LimitRule extends AbstractRules implements QueryRulesInterface
{
    public function __construct(
        private array $limitMap,
        EntityManagerInterface $em,
        ?string $method,
        QueryState $state,
        ?string $customAlias = null,
    ) {
        parent::__construct($em, $method, $customAlias, $state);
    }

    public function getRule(array $limitMap): string
    {
        // Normalize the input array
        $normalized = $this->normalize($limitMap);

        if (empty($normalized)) {
            return '';
        }

        $limitValue = $normalized[0];
        $parameterName = $this->createParameter($limitValue, 'limit', $this->em->getTableAliasHelper(), null, $this->em->getEntity());

        return "{$parameterName}";
    }

    protected function normalize(array $arrayInput): array
    {
        // Remove method key if present
        if (isset($arrayInput['method'])) {
            unset($arrayInput['method']);
        }

        // Extract limit value
        if (isset($arrayInput['limit'])) {
            $value = $arrayInput['limit'];
        } elseif (!empty($arrayInput) && is_numeric($arrayInput[0] ?? null)) {
            $value = $arrayInput[0];
        } else {
            return []; // No limit specified
        }

        // Validate
        if (!is_numeric($value) || $value <= 0) {
            throw new InvalidArgumentException('LIMIT must be a positive integer');
        }

        return [(int) $value];
    }
}