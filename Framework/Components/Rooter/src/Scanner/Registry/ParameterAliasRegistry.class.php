<?php

declare(strict_types=1);

readonly class ParameterAliasRegistry
{
    private const ALIASES = [
        'id' => ['public_id', 'product_id', 'item_id', 'uuid', 'guid', 'key', 'record_id'],
        'slug' => ['name', 'title', 'permalink', 'url', 'path', 'seo_name'],
        'page' => ['p', 'page_num', 'currentPage', 'current_page', 'pg', 'page_number'],
        'limit' => ['per_page', 'size', 'page_size', 'results_per_page', 'take', 'count'],
        'offset' => ['skip', 'start', 'start_index'],
        'token' => ['access_token', 'auth_token', 'bearer_token', 'api_token'],
        'email' => ['username', 'login', 'user_email', 'email_address'],
        'sort' => ['order_by', 'sort_by', 'order'],
        'direction' => ['order_direction', 'sort_direction', 'dir'],
        'search' => ['q', 'query', 'filter', 'keyword'],
        'category' => ['cat', 'category_id', 'cat_id'],
        'status' => ['state', 'active', 'enabled'],
    ];

    public function getAliasesFor(string $parameterName): array
    {
        return self::ALIASES[$parameterName] ?? [];
    }

    public function getAllAliases(): array
    {
        return self::ALIASES;
    }
}