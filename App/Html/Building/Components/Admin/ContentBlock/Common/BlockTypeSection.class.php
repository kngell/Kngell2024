<?php

declare(strict_types=1);

enum BlockTypeSection: string
{
    case MEDIA = 'media';
    case BASICS = 'basic infos';
    case CUSTOM_CONTENT = 'custom content override';
    case PRODUCT = 'product relationship';
    case METADATA = 'metadata';
}