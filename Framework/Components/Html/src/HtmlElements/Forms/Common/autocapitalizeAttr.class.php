<?php

declare(strict_types=1);

enum autocapitalizeAttr : string
{
    case OFF = 'none';
    case ON = 'sentences';
    case WORDS = 'woeds';
    case CHARACTERS = 'characters';
}