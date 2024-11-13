<?php

declare(strict_types=1);
enum RouteArgumentType : string
{
    case REQUEST = 'REQUEST';
    case PATH_VARIABLE = 'PATH_VARIABLE';
    case QUERY_PARAM = 'QUERY_PARAM';
    case HEADER_PARAM = 'HEADER_PARAM';
    case REQUEST_BODY = 'REQUEST_BODY';
}