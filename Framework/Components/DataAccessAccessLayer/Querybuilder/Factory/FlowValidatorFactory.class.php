<?php

declare(strict_types=1);

class FlowValidatorFactory
{
    /** @var FlowValidatorFactoryInterface[] */
    private array $factories;

    public function __construct(
        private SqlComponent $component,
    ) {
        $this->factories = $this->factories();
    }

    public function create(SqlStatementType $statementType): ?FlowValidatorInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($statementType)) {
                return $factory->create();
            }
        }
        return null;
    }

    /**
     * @return FlowValidatorFactoryInterface[]
     */
    private function factories(): array
    {
        return [
            new DataQueryFlowValidatorFactory(
                $this->component,
            ),
            new DataManipulationFlowValidatorFactory(
                $this->component,
            ),
        ];
    }
}
