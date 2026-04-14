<?php

declare(strict_types=1);

trait QueryResultFetchTrait
{
    public function all(): array
    {
        $this->initialize();
        $this->operation = 'all';

        $results = $this->fetcher->fetchAll();
        return $this->paginator->applyPagination($results, 'all');
    }

    public function first(): mixed
    {
        $this->initialize();
        $this->operation = 'first';

        if ($this->paginator->getLimit() !== null && $this->paginator->getLimit() > 1) {
            $results = $this->fetcher->fetchAll();
            $limited = array_slice($results, 0, $this->paginator->getLimit());
            return $limited[0] ?? null;
        }

        return $this->fetcher->fetchFirst();
    }

    public function last(): mixed
    {
        $this->initialize();
        $this->operation = 'last';

        if ($this->paginator->getLastLimit() !== null && $this->paginator->getLastLimit() > 1) {
            $results = $this->fetcher->fetchAll();
            $limited = array_slice($results, -$this->paginator->getLastLimit());
            return $limited[0] ?? null;
        }

        $results = $this->fetcher->fetchAll();
        return !empty($results) ? end($results) : null;
    }

    public function single(): mixed
    {
        $this->initialize();
        $this->operation = 'single';
        return $this->fetcher->fetchSingle();
    }
}
