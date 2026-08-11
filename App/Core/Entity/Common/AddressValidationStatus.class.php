<?php

declare(strict_types=1);

enum AddressValidationStatus: string
{
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case CORRECTED = 'corrected';
    case FAILED = 'failed';
    case NOT_REQUIRED = 'not_required';
}