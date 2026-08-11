<?php

declare(strict_types=1);
enum OperationType: string
{
    case INSERT = 'insert';
    case UPDATE = 'update';
    case DELETE = 'delete';
}
