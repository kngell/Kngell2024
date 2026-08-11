<?php

declare(strict_types=1);

enum CheckoutAddressSectionKeys: string
{
    case RECIPIENT = 'recipient_information';
    case ADDRESS = 'address_details';
    case PREFERENCES = 'address_preferences';
}