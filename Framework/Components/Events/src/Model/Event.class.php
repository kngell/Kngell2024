<?php

declare(strict_types=1);
readonly class Event
{
    private string $id;
    private array $metaData;

    /**
     * @param string $id
     * @param array $metaData
     */
    public function __construct(string $id, array $metaData)
    {
        $this->id = $id;
        $this->metaData = $metaData;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return array
     */
    public function getMetaData(): array
    {
        return $this->metaData;
    }
}