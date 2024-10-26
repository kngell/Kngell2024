<?php

declare(strict_types=1);

readonly class ServiceContainer implements ServiceContainerInterface
{
    /**
     * @var Map<string,BeanInfo>
     */
    private Map $beans;

    /**
     * @param Map $beans
     */
    public function __construct(Map $beans)
    {
        $this->beans = $beans;
    }

    public function getAllBeansByAttributeType(string $attributeType): array
    {
        $beans = [];
        /**
         * @var string $key
         * @var BeanInfo $bean
         */
        foreach ($this->beans->getAll() as $key => $bean) {
            if ($bean->getAttributeType() !== $attributeType) {
                continue;
            }
            $beans[$key] = $bean;
        }
        return $beans;
    }

    public function getFirstBeanByClass(string $class): mixed
    {
        /**
         * @var BeanInfo $bean
         */
        foreach ($this->beans->getAll() as $bean) {
            if (in_array($class, $bean->getPossibleInjectionNames())) {
                return $bean->getService();
            }
        }
        return null;
    }

    public function getBeanInfo(string $id): BeanInfo
    {
        if (!$this->has($id)) {
            throw new BeanNotFoundException("Could not find any class with id : $id");
        }
        return $this->beans->get($id);
    }

    public function get(string $id): object
    {
        if (!$this->has($id)) {
            throw new BeanNotFoundException("Could not find any class with id : $id");
        }
        return $this->beans->get($id)->getService();
    }

    public function has(string $id): bool
    {
        return $this->beans->has($id);
    }
}