<?php

declare(strict_types=1);

enum ResponseBodyType:string
{
    case RAW = 'RAW';
    case JSON = 'JSON';
    case XML = 'XML';
}