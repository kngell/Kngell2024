<?php

declare(strict_types=1);

class SqlExpression extends SqlComponent implements ClauseComponentInterface
{
    public function __construct(
        private mixed $expression,
        ?string $method = null,
        ?EntityManagerInterface $em = null,
    ) {
        parent::__construct(null, $em);
        $this->method = $method;
    }

    #[Override]
    public function build(): string
    {
        if (is_scalar($this->expression)) {
            if (is_string($this->expression)) {
                if ($this->isRawExpression($this->expression)) {
                    return $this->expression;
                }
                return "'" . addslashes($this->expression) . "'";
            }
            if (is_bool($this->expression)) {
                return $this->expression ? 'TRUE' : 'FALSE';
            }
            if ($this->expression === null) {
                return 'NULL';
            }
            return (string) $this->expression;
        }

        // Handle Closure - build as subquery
        if ($this->expression instanceof Closure) {
            $closure = new SqlClosure($this->em, $this->expression);
            $this->prepareChild($closure);
            return '(' . $closure->build() . ')';
        }

        // Handle subquery
        if ($this->expression instanceof SqlSelectQuery) {
            $this->prepareChild($this->expression);
            return '(' . $this->expression->build() . ')';
        }

        // Handle other SqlComponent
        if ($this->expression instanceof SqlComponent) {
            $this->prepareChild($this->expression);
            return $this->expression->build();
        }

        // Handle array (for IN clauses)
        if (is_array($this->expression)) {
            if (empty($this->expression)) {
                return '';
            }
            $values = array_map(function ($value) {
                if (is_string($value)) {
                    return "'" . addslashes($value) . "'";
                }
                if ($value === null) {
                    return 'NULL';
                }
                if ($value instanceof SqlComponent) {
                    $this->prepareChild($value);
                    $result = $value->build();
                    $this->mergeChildState($value);
                    return $result;
                }
                return (string) $value;
            }, $this->expression);
            return implode(', ', $values);
        }

        return (string) $this->expression;
    }

    private function isRawExpression(string $value): bool
    {
        $patterns = [
            '/^[a-zA-Z_][a-zA-Z0-9_]*\(.*\)$/',  // Function: NOW(), COUNT(*)
            '/^[a-zA-Z_][a-zA-Z0-9_]*\.[a-zA-Z_][a-zA-Z0-9_]*$/', // Column: table.column
            '/^[0-9]+$/', // Numbers
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }
}