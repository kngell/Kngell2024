<?php

declare(strict_types=1);

class ConditionGroupBuilder
{
    private CollectionInterface $groupedElements;
    private ?string $lastLogicalLink = null;

    public function __construct(private EntityManagerInterface $em)
    {
        $this->groupedElements = new Collection();
    }

    public function addCondition(string $method, array $conditions): void
    {
        $logicalLink = $this->getLogicalLinkFromMethod($method);
        $condition = $this->createConditionElement($method, $conditions);

        if ($condition === null) {
            return;
        }

        $this->addConditionToCollection($condition, $logicalLink);
    }

    public function getGroupedElements(): CollectionInterface
    {
        return $this->groupedElements;
    }

    public function getGroupLink(int $groupIndex): string
    {
        return 'AND';
    }

    private function createConditionElement(string $method, array $conditions): ?SqlComponent
    {
        if ($this->containsClosure($conditions)) {
            return $this->hasRegularConditions($conditions)
                ? $this->handleMixedClosureCondition($method, $conditions)
                : $this->processPureClosure($conditions, $method);
        }

        return new ConditionClause($conditions, $method, $this->em);
    }

    private function addConditionToCollection(SqlComponent $condition, string $logicalLink): void
    {
        if ($this->groupedElements->isEmpty()) {
            $this->addFirstCondition($condition, $logicalLink);
            return;
        }

        if ($logicalLink === 'OR') {
            $this->handleOrCondition($condition);
            return;
        }

        $this->addAndCondition($condition, $logicalLink);
    }

    private function addFirstCondition(SqlComponent $condition, string $logicalLink): void
    {
        $condition->setLogicalLink($logicalLink);
        $this->groupedElements->add($condition);
        $this->lastLogicalLink = $logicalLink;
    }

    private function handleOrCondition(SqlComponent $condition): void
    {
        $lastElement = $this->groupedElements->removeLast();

        $orGroup = new ConditionGroup(SqlClause::WHERE);
        $orGroup->setLogicalLink($this->lastLogicalLink ?? 'AND');

        // Add the last element with AND link
        if ($lastElement instanceof ConditionClause || $lastElement instanceof ConditionGroup) {
            $lastElement->setLogicalLink('AND');
            $orGroup->add($lastElement);
        }

        // Add the new OR condition

        $condition->setLogicalLink('OR');
        $orGroup->add($condition);

        $this->groupedElements->add($orGroup);
        $this->lastLogicalLink = 'AND';
    }

    private function addAndCondition(SqlComponent $condition, string $logicalLink): void
    {
        $condition->setLogicalLink($logicalLink);
        $this->groupedElements->add($condition);
        $this->lastLogicalLink = $logicalLink;
    }

    private function containsClosure(array $conditions): bool
    {
        foreach ($conditions as $condition) {
            if ($condition instanceof Closure) {
                return true;
            }
        }
        return false;
    }

    private function handleMixedClosureCondition(string $method, array $conditions): ConditionGroup
    {
        $mixedGroup = new ConditionGroup(SqlClause::WHERE);
        $mixedGroup->setIsExplicit(true);
        $mixedGroup->setLogicalLink($this->getLogicalLinkFromMethod($method));

        list($regularConditions, $closures) = $this->separateConditions($conditions);

        // Add regular conditions
        if (!empty($regularConditions)) {
            $regularCondition = new ConditionClause($regularConditions, $method, $this->em);
            $regularCondition->setLogicalLink('AND');
            $mixedGroup->add($regularCondition);
        }

        // Add closure conditions
        foreach ($closures as $closure) {
            $closureElement = $this->processSingleClosure($closure, $method);
            if ($closureElement !== null) {
                $closureElement->setLogicalLink('AND');
                $mixedGroup->add($closureElement);
            }
        }

        return $mixedGroup;
    }

    private function processPureClosure(array $conditions, string $method): ?SqlComponent
    {
        $nestedElements = $this->processClosuresToElements($conditions);

        if ($nestedElements->isEmpty()) {
            return null;
        }

        // If only one element, return it directly (no group needed)
        if ($nestedElements->size() === 1) {
            $element = $nestedElements->first();
            $element->setLogicalLink($this->getLogicalLinkFromMethod($method));
            return $element;
        }

        // Multiple elements - wrap in group
        $parentGroup = new ConditionGroup(SqlClause::WHERE);
        $parentGroup->setLogicalLink($this->getLogicalLinkFromMethod($method));

        foreach ($nestedElements->all() as $element) {
            $parentGroup->add($element);
        }

        return $parentGroup;
    }

    private function processSingleClosure(Closure $closure, string $method): ?SqlComponent
    {
        $nestedQuery = new SqlSelectQuery($this->em);
        $closure($nestedQuery);
        $nestedConditions = $nestedQuery->getWhereConditions();

        if (empty($nestedConditions['where'])) {
            return null;
        }

        $nestedBuilder = new ConditionGroupBuilder($this->em);
        foreach ($nestedConditions['where'] as $nestedConditionData) {
            $nestedBuilder->addCondition(
                $nestedConditionData['method'],
                $nestedConditionData['conditions'],
            );
        }

        $nestedElements = $nestedBuilder->getGroupedElements();

        if ($nestedElements->isEmpty()) {
            return null;
        }

        // Single nested element - return directly
        if ($nestedElements->size() === 1) {
            $element = $nestedElements->first();
            $element->setLogicalLink($this->getLogicalLinkFromMethod($method));
            return $element;
        }

        // Multiple nested elements - wrap in group
        $closureGroup = new ConditionGroup(SqlClause::WHERE);
        $closureGroup->setLogicalLink($this->getLogicalLinkFromMethod($method));

        foreach ($nestedElements->all() as $element) {
            $closureGroup->add($element);
        }

        return $closureGroup;
    }

    private function processClosuresToElements(array $conditions): CollectionInterface
    {
        $nestedBuilder = new ConditionGroupBuilder($this->em);

        foreach ($conditions as $condition) {
            if ($condition instanceof Closure) {
                $nestedQuery = new SqlSelectQuery($this->em);
                $condition($nestedQuery);
                $nestedConditions = $nestedQuery->getWhereConditions();

                foreach ($nestedConditions['where'] ?? [] as $nestedConditionData) {
                    $nestedBuilder->addCondition(
                        $nestedConditionData['method'],
                        $nestedConditionData['conditions'],
                    );
                }
            }
        }

        return $nestedBuilder->getGroupedElements();
    }

    private function separateConditions(array $conditions): array
    {
        $regularConditions = [];
        $closures = [];

        foreach ($conditions as $condition) {
            if ($condition instanceof Closure) {
                $closures[] = $condition;
            } else {
                $regularConditions[] = $condition;
            }
        }

        return [$regularConditions, $closures];
    }

    private function hasRegularConditions(array $conditions): bool
    {
        foreach ($conditions as $condition) {
            if (!$condition instanceof Closure) {
                return true;
            }
        }
        return false;
    }

    private function getLogicalLinkFromMethod(string $method): string
    {
        return SqlBuilderMethodRegistry::getLogicalLink($method)->name;
    }
}