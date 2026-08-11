<?php

declare(strict_types=1);

enum HeroSectionEnum: string
{
    case BASIC_INFO = 'basics information';
    case CALL_TO_ACTION = 'call to action';
    case MEDIA = 'media';
    case METADATA = 'metadata';
}