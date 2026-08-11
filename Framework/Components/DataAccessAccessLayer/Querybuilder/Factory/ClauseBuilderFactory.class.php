<?php

declare(strict_types=1);

class ClauseBuilderFactory
{
    /** @var ClauseBuilderFactoryInterface[] */
    private array $factories;

    public function __construct(
        private SqlComponent $component,
    ) {
        $this->factories = $this->factories();
    }

    public function create(SqlStatement $statement): ?ClauseBuilderInterface
    {
        foreach ($this->factories as $factory) {
            if ($factory->supports($statement)) {
                return $factory->create();
            }
        }
        return null;
    }

    /**
     * @return ClauseBuilderFactoryInterface[]
     */
    private function factories(): array
    {
        return [
            new DataQueryClauseBuilderFactory(
                $this->component,
            ),
            // new DataManipulationClauseBuilderFactory(
            //     $this->component,
            // ),
        ];
    }
}