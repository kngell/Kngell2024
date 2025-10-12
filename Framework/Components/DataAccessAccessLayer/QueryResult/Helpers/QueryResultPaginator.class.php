<?php

declare(strict_types=1);

class QueryResultPaginator
{
    private ?int $lastLimit = null;

    private ?int $limit = null;

    private ?array $pagination = null;

    public function applyPagination(array $results, string $operation): array
    {
        if ($this->limit !== null && $operation === 'all') {
            return array_slice($results, 0, $this->limit);
        }

        if ($this->lastLimit !== null && $operation === 'all') {
            return array_slice($results, -$this->lastLimit);
        }

        if ($this->pagination !== null && $operation === 'all') {
            $offset = ($this->pagination['page'] - 1) * $this->pagination['perPage'];
            return array_slice($results, $offset, $this->pagination['perPage']);
        }

        return $results;
    }

    public function getLastLimit(): ?int
    {
        return $this->lastLimit;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getPagination(): ?array
    {
        return $this->pagination;
    }

    public function hasPagination(): bool
    {
        return $this->limit !== null || $this->lastLimit !== null || $this->pagination !== null;
    }

    public function setLastLimit(int $limit): self
    {
        $this->lastLimit = $limit;
        return $this;
    }

    public function setLimit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function setPagination(int $page, int $perPage): self
    {
        $this->pagination = ['page' => $page, 'perPage' => $perPage];
        return $this;
    }
}