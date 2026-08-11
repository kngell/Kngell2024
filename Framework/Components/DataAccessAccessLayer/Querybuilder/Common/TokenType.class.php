<?php

declare(strict_types=1);

enum TokenType: string
{
    case KEYWORD = 'keyword';
    case FUNCTION = 'function';
    case IDENTIFIER = 'identifier';
    case LITERAL = 'literal';
    case OPERATOR = 'operator';
    case PUNCTUATION = 'punctuation';
    case PARAMETER = 'parameter';
}