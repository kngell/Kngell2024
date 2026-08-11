<?php

declare(strict_types=1);

final class FooterColumnsPaginatedAdapter extends AbstractPaginatedAdapter
{
    protected string $identifierPrefix = 'f_';
    protected array $searchFields = ['title', 'column_key'];

    public function __construct(
        FooterMenuShowModel $model,
        array $filters = [],
        array $sort = ['title' => 'ASC'],
    ) {
        parent::__construct($model, $filters, $sort);
    }

    public function getEntityClass(): string
    {
        return FooterMenuShow::class;
    }
}