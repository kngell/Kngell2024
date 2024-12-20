<?php

declare(strict_types=1);

class ConcreteFactory2 extends AbstractFactory
{
    public function createProduct1()
    {
        return new Product1_2();
    }

    public function createProduct2()
    {
        return new Product2_2();
    }
}