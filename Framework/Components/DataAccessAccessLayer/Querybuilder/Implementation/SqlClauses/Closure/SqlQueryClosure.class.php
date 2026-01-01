<?php

// In SqlQeurySelectClosure.class.php

declare(strict_types=1);

class SqlQueryClosure extends SqlComponent
{
    private Closure $closure;

    public function __construct(Closure $closure)
    {
        parent::__construct();

        $this->closure = $closure;
    }

    public function build(): string
    {
        $query = ($this->closure)($this);
        $this->prepareChild($query);
        $this->query = $query->build();
        $this->mergeChildState($query);
        return '(' . $this->query . ') AS subquery_alias';
    }
}
