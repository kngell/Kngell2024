<?php

declare(strict_types=1);

enum FieldType: string
{
    case STRING = 'string';
    case TEXT = 'text';
    case NUMBER = 'number';
    case EMAIL = 'email';
    case PASSWORD = 'password';
    case DATE = 'date';
    case TIME = 'time';
    case DATETIME = 'datetime-local';
    case INTEGER = 'integer';
    case FLOAT = 'float';
    case BOOLEAN = 'boolean';
    case INT = 'int';
}