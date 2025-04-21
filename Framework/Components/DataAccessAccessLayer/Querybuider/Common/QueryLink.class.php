<?php

declare(strict_types=1);

enum QueryLink : string
{
    case AND = 'and';
    case OR = 'or';
    case IN = 'IN';
}