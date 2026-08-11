<?php

declare(strict_types=1);

class CaseExpressionBlock extends SqlQuery
{
    private const SqlKeyword KEY_WORD = SqlKeyword::CASE;

    private bool $hasCase = false;
    private bool $pendingWhen = false;
    private bool $hasElse = false;
    private int $whenThenCount = 0;
    private ?string $endAs = null;

    public function __construct(
        private array $caseMap,
        array $queryFlow,
        ?SqlStatement $parentStatement = null,
        ?EntityManagerInterface $em = null,
    ) {
        parent::__construct(null, $parentStatement, $em);
        $this->queryFlow = $queryFlow;
        $this->initialize();
        $this->initializeComponents();
        $this->initializeWithDependencies(
            $em?->getTableAliasHelper(),
            $this->getState(),
            $this->customAlias,
            $this->queryMap,
        );
    }

    public function build(): string
    {
        $this->validateCaseBlock();

        $parts = [];

        if (isset($this->caseMap['case'])) {
            $parts[] = self::KEY_WORD->value;
        }

        $childrenSql = parent::build();
        if ($childrenSql) {
            $parts[] = $childrenSql;
        }

        if (isset($this->caseMap['case'])) {
            $end = SqlKeyword::END->value;
            if ($this->endAs !== null) {
                $end .= ' AS ' . $this->endAs;
            }
            $parts[] = $end;
        }

        $this->query = implode(' ', array_filter($parts));
        return $this->query;
    }

    protected function validateQueryFlow(): void
    {
        $flow = $this->queryFlow;
        $whenIndex = array_search('when', $flow, true);
        $thenIndex = array_search('then', $flow, true);

        if ($whenIndex !== false && $thenIndex !== false && $whenIndex > $thenIndex) {
            throw new QueryBuildException('WHEN must come before THEN');
        }
    }

    private function initialize(): void
    {
        $this->validateQueryFlow();
        $this->buildCaseElements();
    }

    private function buildCaseElements(): void
    {
        foreach ($this->caseMap as $key => $value) {
            if ($key === 'case') {
                $this->buildCase($value);
            }
            if ($key === 'when_then') {
                foreach ($value as $whenThen) {
                    $this->buildWhenThen($whenThen);
                }
            }
            if ($key === 'else') {
                $this->buildElse(...$value);
            }
            if ($key === 'end') {
                $this->buildEnd($value);
            }
        }
    }

    private function buildCase(mixed $expression): void
    {
        if ($this->hasCase) {
            throw new QueryBuildException('CASE can only be called once');
        }

        if ($expression !== null && $expression !== '') {
            $this->add(new SqlExpression(
                expression: $expression,
                method: __FUNCTION__,
                em: $this->em,
            ));
        }
        $this->hasCase = true;
    }

    private function buildWhenThen(array $whenThen): void
    {
        $conditions = $whenThen['when'] ?? [];
        $result = $whenThen['then'] ?? null;

        if (empty($conditions)) {
            throw new QueryBuildException('WHEN clause has no conditions');
        }

        $conditionBuilder = new ConditionBuilderHelper($this->em, $conditions);
        $whenThenComponent = new CaseWhenThen(
            builder: $conditionBuilder->getBuilder(),
            result: $result,
            em: $this->em,
        );

        $this->add($whenThenComponent);
        $this->whenThenCount++;
        $this->pendingWhen = false;
    }

    private function buildElse(mixed ...$sqlExpression): void
    {
        if ($this->pendingWhen) {
            throw new QueryBuildException('Missing THEN before ELSE');
        }

        if ($this->hasElse) {
            throw new QueryBuildException('ELSE can only be called once');
        }

        $else = new CaseElse(
            result: $sqlExpression,
            em: $this->em,
        );

        $this->add($else);
        $this->hasElse = true;
    }

    private function buildEnd(?string $as = null): void
    {
        $this->endAs = $as;
    }

    private function buildExpression(mixed $expression): string
    {
        if (is_scalar($expression)) {
            if (is_string($expression)) {
                if ($this->isRawExpression($expression)) {
                    return $expression;
                }
                return "'" . addslashes($expression) . "'";
            }
            if (is_bool($expression)) {
                return $expression ? 'TRUE' : 'FALSE';
            }
            if ($expression === null) {
                return 'NULL';
            }
            return (string) $expression;
        }

        if ($expression instanceof SqlComponent) {
            $this->prepareChild($expression);
            return $expression->build();
        }

        if ($expression instanceof Closure) {
            $closure = new SqlClosure($this->em, $expression);
            $this->prepareChild($closure);
            return '(' . $closure->build() . ')';
        }

        if (is_array($expression)) {
            $values = array_map(fn ($v) => $this->buildExpression($v), $expression);
            return implode(', ', $values);
        }

        return (string) $expression;
    }

    private function isRawExpression(string $value): bool
    {
        $patterns = [
            '/^[a-zA-Z_][a-zA-Z0-9_]*\(.*\)$/',      // Function: NOW(), COUNT(*)
            '/^[a-zA-Z_][a-zA-Z0-9_]*\.[a-zA-Z_][a-zA-Z0-9_]*$/', // Column: table.column
            '/^[0-9]+$/',                            // Numbers
            '/^:[a-zA-Z_][a-zA-Z0-9_]*$/',          // Parameters: :param_name
            '/^[a-zA-Z_][a-zA-Z0-9_]*$/',            // Simple identifiers
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }

        return false;
    }

    private function validateCaseBlock(): void
    {
        // if ($this->whenThenCount === 0) {
        //     throw new QueryBuildException('CASE must have at least one WHEN-THEN clause');
        // }

        if ($this->pendingWhen) {
            throw new QueryBuildException('WHEN clause without matching THEN');
        }
    }
}