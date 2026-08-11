<?php

declare(strict_types=1);

class SmallBannerShowModel extends Model
{
    public function countAdminList(array $conditions = []): int
    {
        $conditions = array_merge(
            $conditions,
            [ConditionListMode::MODE_ADMIN->value => true],
        );
        return parent::count($conditions);
    }
}