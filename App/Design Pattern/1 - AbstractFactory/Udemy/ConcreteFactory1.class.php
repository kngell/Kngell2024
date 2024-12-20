<?php

declare(strict_types=1);

class ConcreteFactory1 extends AbstractFactory
{
    public function createProduct1()
    {
        return new Product1_1();
    }

    public function createProduct2()
    {
        return new Product2_1();
    }
}