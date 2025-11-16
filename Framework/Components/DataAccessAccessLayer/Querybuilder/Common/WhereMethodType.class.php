<?php

declare(strict_types=1);
enum WhereMethodType: string
{
    public function toSqlClause(): SqlClause
    {
        return match ($this) {
            self::WHERE => SqlClause::WHERE,
            self::ON => SqlClause::WHERE,
            self::IN => SqlClause::WHERE,
            self::OR => SqlClause::WHERE,
            self::AND => SqlClause::WHERE
        };
    }

    case WHERE = 'where';
    case OR = 'orWhere';
    case AND = 'andWhere';
    case NOT_EQUAL_TO = 'whereNotEqualTo';
    case IN = 'whereIn';
    case NOT_IN = 'whereNotIn';
    case ON = 'on';
}