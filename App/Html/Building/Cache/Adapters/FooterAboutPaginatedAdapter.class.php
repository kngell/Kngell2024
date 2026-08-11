<?php

declare(strict_types=1);

final class FooterAboutPaginatedAdapter extends AbstractPaginatedAdapter
{
    protected string $identifierPrefix = 'f_';
    protected array $searchFields = ['logo_icon'];

    public function __construct(
        FooterAboutModel $model,
        array $filters = [],
        array $sort = ['logo_icon' => 'ASC'],
    ) {
        parent::__construct($model, $filters, $sort);
    }

    public function getEntityClass(): string
    {
        return FooterAbout::class;
    }
}