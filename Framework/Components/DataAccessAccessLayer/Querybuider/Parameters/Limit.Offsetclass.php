<?php

declare(strict_types=1);
class LimitOffset extends MainQuery
{
    public function __construct(EntityManagerInterface $em, private int $limit)
    {
        $this->em = $em;
    }

    public function getSql(): array
    {
        $tblh = $this->em->getTableAliasHelper();
        $stmt = $tblh->getToken()->generate(2, $this->method);
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