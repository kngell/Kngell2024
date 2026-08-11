<?php

declare(strict_types=1);

enum OperationStatus: string
{
    case SUCCESS = 'success';
    case NO_DATA = 'no_data';
    case VALIDATION_ERROR = 'validation_error';
    case FILE_STORAGE_ERROR = 'file_storage_error';
    case DATABASE_ERROR = 'database_error';
}