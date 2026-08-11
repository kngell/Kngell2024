<?php

declare(strict_types=1);

enum SqlConditionLink: string
{
    public function isLogical(): bool
    {
        return in_array($this, [self::AND, self::OR, self::XOR]);
    }

    public function isJoinConnective(): bool
    {
        return $this === self::ON;
    }

    public function toSql(): string
    {
        return $this->value;
    }

    public static function getFrom(string $method): self
    {
        $mapping = SqlBuilderMethodRegistry::getMapping($method);
        return $mapping['link'] ?? self::AND;
    }
    case AND = 'AND';
    case OR = 'OR';
    case XOR = 'XOR';
    case ON = 'ON';
}