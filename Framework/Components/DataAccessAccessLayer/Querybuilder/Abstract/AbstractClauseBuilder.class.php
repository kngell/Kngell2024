<?php

declare(strict_types=1);

class AbstractClauseBuilder
{
    protected function validateAllowedMethods(array $userFlow, SqlStatementType $statementType): void
    {
        foreach ($userFlow as $method) {
            if (!$statementType->isMethodAllowed($method)) {
                $allowedCategories = $statementType->getAllowedCategories();
                $allowedMethods = [];

                foreach ($allowedCategories as $category) {
                    $allowedMethods = array_merge($allowedMethods, $category->getMethods());
                }

                throw new QueryFlowException(
                    "Method '{$method}' is not allowed for {$statementType->value} statements. " .
                    'Allowed methods: ' . implode(', ', array_unique($allowedMethods)),
                );
            }
        }
    }

    protected function validateCategoryOrder(array $userFlow, array $categoryOrder): void
    {
        $userCategories = $this->getUserCategories($userFlow);
        $lastValidIndex = -1;

        foreach ($userCategories as $userCategory) {
            $currentIndex = $this->getCategoryIndex($userCategory, $categoryOrder);

            if ($currentIndex === false) {
                throw new QueryFlowException(
                    "Category '{$userCategory->value}' is not valid for this query flow. " .
                    'Valid categories: ' . implode(', ', array_map(fn ($c) => $c->value, $categoryOrder)),
                );
            }

            if ($currentIndex < $lastValidIndex) {
                $expectedCategory = $categoryOrder[$lastValidIndex];
                $actualCategory = $categoryOrder[$currentIndex];
                throw new QueryFlowException(
                    "Category '{$actualCategory->value}' cannot appear after '{$expectedCategory->value}'. " .
                    'Correct order: ' . implode(' → ', array_map(fn ($c) => $c->value, $categoryOrder)),
                );
            }

            $lastValidIndex = $currentIndex;
        }
    }

    private function getUserCategories(array $userFlow): array
    {
        $categories = [];
        foreach ($userFlow as $method) {
            $category = SqlClauseCategory::getCategoryForMethod($method);
            if ($category && !in_array($category, $categories)) {
                $categories[] = $category;
            }
        }
        return $categories;
    }

    private function getCategoryIndex(SqlClauseCategory $category, array $categoryOrder): int|false
    {
        return array_search($category, $categoryOrder);
    }
}