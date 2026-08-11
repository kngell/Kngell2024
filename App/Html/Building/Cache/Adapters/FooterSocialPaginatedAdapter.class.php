<?php

declare(strict_types=1);

final class FooterSocialPaginatedAdapter extends AbstractPaginatedAdapter
{
    protected string $identifierPrefix = 'f_';
    protected array $searchFields = ['name', 'platform'];

    public function __construct(
        FooterSocialModel $model,
        array $filters = [],
        array $sort = ['name' => 'ASC'],
    ) {
        parent::__construct($model, $filters, $sort);
    }

    public function getEntityClass(): string
    {
        return FooterSocial::class;
    }
}