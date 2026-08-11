<?php

declare(strict_types=1);

class CaseElse extends SqlComponent
{
    private const SqlKeyword KEY_WORD = SqlKeyword::ELSE;

    private SqlExpression $result;

    public function __construct(
        mixed $result,
        ?string $method = null,
        ?EntityManagerInterface $em = null,
    ) {
        parent::__construct(null, $em);
        $this->method = $method;
        $this->result = new SqlExpression(
            expression: $result,
            method: $method,
            em: $em,
        );
    }

    #[Override]
    public function build(): string
    {
        $parts = [self::KEY_WORD->value];
        $this->prepareChild($this->result);
        $this->query = $this->result->build();
        $this->mergeChildState($this->result);
        $parts[] = $this->query;
        return implode(' ', $parts);
    }
}