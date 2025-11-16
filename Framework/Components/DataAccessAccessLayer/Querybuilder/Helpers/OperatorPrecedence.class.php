<?php

declare(strict_types=1);

/**
 * OperatorPrecedence - Handles SQL operator precedence for condition grouping.
 */
final class OperatorPrecedence
{
    /**
     * Check if operator precedence requires parentheses between conditions.
     */
    public static function requiresParentheses(
        ?SqlOperator $previous,
        ?SqlOperator $current,
        string $currentLogicalLink,
    ): bool {
        // Always group OR conditions
        if ($currentLogicalLink === 'OR') {
            return true;
        }

        // No previous operator = first condition, no parentheses needed
        if ($previous === null || $current === null) {
            return false;
        }

        $previousPrecedence = $previous->getPrecedence();
        $currentPrecedence = $current->getPrecedence();

        // Group when moving from higher to lower precedence
        if ($previousPrecedence > $currentPrecedence) {
            return true;
        }

        // Special case: AND after OR always needs grouping
        if ($previous === SqlOperator::OR && $currentLogicalLink === 'AND') {
            return true;
        }

        // Mixed logical operators need grouping
        if ($previous->isLogical() && $current->isLogical() && $previous !== $current) {
            return true;
        }

        return false;
    }

    /**
     * Check if a condition group needs parentheses based on internal operator mix.
     */
    public static function groupNeedsParentheses(array $conditions): bool
    {
        if (count($conditions) <= 1) {
            return false;
        }

        $hasOr = false;
        $hasAnd = false;
        $previousOperator = null;

        foreach ($conditions as $condition) {
            $currentOperator = $condition->getOperator();
            $logicalLink = $condition->getLogicalLink();

            // Track operator types
            if ($logicalLink === 'OR') {
                $hasOr = true;
            }
            if ($logicalLink === 'AND') {
                $hasAnd = true;
            }

            // Check precedence conflicts
            if ($previousOperator && self::requiresParentheses($previousOperator, $currentOperator, $logicalLink)) {
                return true;
            }

            $previousOperator = $currentOperator;
        }

        // Mixed AND/OR always needs parentheses
        return $hasOr && $hasAnd;
    }

    /**
     * Get the appropriate logical operator between conditions.
     */
    public static function getLogicalOperator(OperatorAwareInterface $condition): string
    {
        return $condition->getLogicalLink();
    }
}