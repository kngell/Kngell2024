<?php

declare(strict_types=1);

class UserAddressRepository extends Repository
{
    protected const array COLUMN_MAPS = [
        'user' => ['user_id', 'first_name', 'last_name', 'is_active'],
        'address' => ['id', 'user_id', 'first_name', 'last_name', 'company', 'phone', 'email', 'address1', 'address2', 'city', 'state', 'postal_code', 'country_code', 'label', 'is_default_shipping', 'is_default_billing', 'address_type', 'is_verified', 'validation_status', 'validation_response', 'validated_at', 'is_active', 'deleted_at', 'created_at', 'updated_at',
        ],
        'country' => ['id', 'iso_code', 'iso3_code', 'numeric_code', 'name', 'official_name', 'phone_prefix', 'region'],
    ];

    public function findOneBy(array $conditions = [], array $columns = []): void
    {
        $mode = $this->extractModeFromConditions($conditions);
        $baseConditions = $this->buildModeConditions($conditions, $mode);
        $baseConditions = $this->removeModeFlags($baseConditions);

        $columns = array_merge($columns, $this->getAllColumns());

        if ($mode === ConditionListMode::MODE_FRONTEND->value) {
            $this->executeFrontendQuery($baseConditions, $columns);
            return;
        }
        parent::findOneBy($baseConditions);
    }

    protected function buildModeConditions(array $conditions, ?string $mode): array
    {
        switch ($mode) {
            case ConditionListMode::MODE_ADMIN->value:
                return $this->applyGlobalScopes($conditions);
            case ConditionListMode::MODE_RESTORABLE->value:
                return array_merge(['deleted_at' => 'is not null'], $conditions);
            case ConditionListMode::MODE_FRONTEND->value:
                $conditions = array_merge(
                    $conditions,
                    ['is_active' => true],
                );
                return $this->applyGlobalScopes($conditions);
            default:
                return $conditions;
        }
    }

    private function executeFrontendQuery(array $conditions, array $columns = [], ?int $limit = null, ?int $offset = null): void
    {
        $qb = $this->em->createQueryBuilder()->setStatementContext(SqlStatement::SELECT);

        $columns = array_merge($columns, [$qb->case(
            $qb->when(
                'address.is_default_shipping',
                true,
                'address.is_default_billing',
                true,
            )->then('Default Shipping & Billing')
            ->when('address.is_default_shipping', 1)
            ->then('Default Shipping')
            ->when('address.is_default_billing', true)
            ->then('Default Billing')
            ->else('Saved Address'),
        )->end('address_type')]);

        $query = $qb->selectWithAlias($columns ?: '*')->from('user')
        ->join('address')
        ->on('user_id', 'address.user_id')
        ->leftjoin('country')
        ->on('address.country_code', 'country.iso_code');

        // $caseConditions = $this->extractCaseConditions($conditions);
        $this->applyMixedConditions($query, $conditions);

        // if (!empty($caseConditions)) {
        //     $query = $query->orderBy($qb->case()->when($caseConditions)->then(0)->else(1)->end(), 'ASC');
        // }

        $query->orderBy(
            'user_id',
            'address.is_default_shipping DESC',
            'address.created_at',
        );

        if ($limit !== null) {
            $query->limit($limit);
        }
        if ($offset !== null) {
            $query->offset($offset);
        }

        $query->build();
        // $this->debugSql($qb);
    }
}