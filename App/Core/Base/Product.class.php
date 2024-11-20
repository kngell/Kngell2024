<?php

declare(strict_types=1);

class Product
{
    public function __construct(private DatabaseConnexionInterface $db)
    {
    }

    public function getData() : array
    {
        $sql = 'SELECT * from product';
        $stmt = $this->db->open()->query($sql);
        return  $stmt->fetchAll();
    }
}