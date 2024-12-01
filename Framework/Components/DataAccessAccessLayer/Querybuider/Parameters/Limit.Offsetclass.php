<?php

declare(strict_types=1);
class LimitOffset extends MainQuery
{
    public function __construct(TablesAliasHelper $tblh, private int $limit)
    {
        $this->tblh = $tblh;
    }

    public function getSql(): array
    {
        $stmt = $this->tblh->getToken()->generate(2, $this->method);
        $rule = ':' . $stmt . '_' . $this->method;
        $this->parameters[$stmt . '_' . $this->method] = $this->limit;
        return [
            $rule,
            $this->tableAlias,
            $this->aliasCheck,
            $this->parameters,
            $this->bind_arr,
        ];
    }
}