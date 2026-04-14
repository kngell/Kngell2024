<?php

declare(strict_types=1);

class CleanupResult
{
    private array $deleted = [];
    private array $candidates = [];
    private array $failed = [];

    public function addDeleted(array $fileInfo): void
    {
        $this->deleted[] = $fileInfo;
    }

    public function addCandidate(array $fileInfo): void
    {
        $this->candidates[] = $fileInfo;
    }

    public function addFailed(array $fileInfo, string $error): void
    {
        $this->failed[] = $fileInfo + ['error' => $error];
    }

    public function merge(CleanupResult $other): void
    {
        $this->deleted = array_merge($this->deleted, $other->deleted);
        $this->candidates = array_merge($this->candidates, $other->candidates);
        $this->failed = array_merge($this->failed, $other->failed);
    }

    public function getDeletedCount(): int
    {
        return count($this->deleted);
    }

    public function getCandidateCount(): int
    {
        return count($this->candidates);
    }

    public function getFailedCount(): int
    {
        return count($this->failed);
    }

    public function getTotalSizeDeleted(): int
    {
        return array_sum(array_column($this->deleted, 'size'));
    }

    public function getTotalSizeCandidates(): int
    {
        return array_sum(array_column($this->candidates, 'size'));
    }

    public function toArray(): array
    {
        return [
            'deleted' => $this->deleted,
            'candidates' => $this->candidates,
            'failed' => $this->failed,
            'summary' => [
                'deleted_count' => $this->getDeletedCount(),
                'deleted_size' => $this->getTotalSizeDeleted(),
                'candidate_count' => $this->getCandidateCount(),
                'candidate_size' => $this->getTotalSizeCandidates(),
                'failed_count' => $this->getFailedCount(),
            ],
        ];
    }
}
