<?php

declare(strict_types=1);
enum QueryType: string
{
    case SELECT = 'select';
    case INSERT = 'insert';
    case UPDATE = 'update';
    case UPDATECTE = 'updateCte';
    case DELETE = 'delete';
    case CREATE = 'create';
    case RAW = 'raw';
    case SHOW = 'show';
    case WITHCTE = 'withCte';

    private const METHODS_FLOW_ARY = [
        'select' => ['select' => true,
            'from' => true,
            'join' => false,
            'on' => false,
            'where' => false,
            'having' => false,
            'groupBy' => false,
            'orderBy' => false,
            'limit' => false,
            'offset' => false, ],
        'insert' => ['insert' => true, 'into' => false, 'fields' => true, 'values' => true],
        'update' => ['update' => true, 'set' => true, 'where' => true],
        'updateCte' => ['update', 'fields', 'join', 'values', 'where'],
        'delete' => ['delete' => true, 'from' => true, 'where' => false],
        'create' => [],
        'raw' => ['raw' => true],
        'show' => ['show'],
        'withCte' => ['with', 'fields', 'values', 'where'],

    ];

    public function getFlow(): array
    {
        return self::METHODS_FLOW_ARY[$this->value] ?? [];
    }

    public static function get(string $type): ?self
    {
        $type = strtoupper($type);
        foreach (self::cases() as $case) {
            if ($case->name === $type) {
                return $case;
            }
        }
        return null;
    }
}