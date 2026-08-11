<?php

declare(strict_types=1);

class SqlClosure extends SqlComponent
{
    public function __construct(?EntityManagerInterface $em, private closure $closure)
    {
        parent::__construct(null, $em);
    }

    #[Override]
    public function build(): string
    {
        $query = ($this->closure)($this);

        if (!($query->getParent() instanceof SqlSelectQuery)) {
            $this->prepareChild($query);
        }

        $this->query = $query->build();
        $this->mergeChildState($query);
        return '(' . $this->query . ')';
    }
}