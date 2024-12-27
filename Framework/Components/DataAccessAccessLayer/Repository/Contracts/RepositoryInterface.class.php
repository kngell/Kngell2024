<?php

declare(strict_types=1);

interface RepositoryInterface
{
    public function create() : void;

    public function update() : void;

    public function delete() : void;

    public function findByID(int $id) : void;

    public function findAll(): void;

    public function findBy(array $conditions = []) : void;

    public function findOneBy(array $conditions = []) : void;

    public function showColumns(string $tableName) : void;
}