<?php

declare(strict_types=1);

use Psr\Container\ContainerInterface;

interface ServiceContainerInterface extends ContainerInterface
{
    /**
     * @param string $attributeType
     * @return BeanInfo[]
     */
    public function getAllBeansByAttributeType(string $attributeType) : array;

    public function getFirstBeanByClass(string $class) : mixed;

    public function getBeanInfo(string $id) : BeanInfo;
}