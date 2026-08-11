<?php

declare(strict_types=1);

abstract class BaseSectionProvider extends AbstractSectionProvider
{
    /** @var array<string, object> */
    private array $prototypeInstances = [];

    public function __construct(
        private RegularPageConfigInterface|SectionConfigInterface|null $config,
        IconBuilder $iconBuilder,
    ) {
        parent::__construct($iconBuilder);
    }

    public function registerSections(HtmlBuilder $html, ?HtmlSectionManagerInterface $manager = null): void
    {
        if ($manager === null) {
            return;
        }

        $sections = $this->getSections();

        if (empty($sections)) {
            return;
        }

        $registeredKeys = [];
        $this->register($manager, $sections, $registeredKeys);
    }

    private function getSections(): array
    {
        $sections = $this->getDirectSections();

        if (!empty($sections)) {
            return $sections;
        }

        return $this->buildSectionsFromConfigs();
    }

    private function getDirectSections(): array
    {
        $sections = $this->config->getSections();

        if (empty($sections)) {
            return [];
        }

        $container = ContainerRegistry::getContainer();
        $instances = [];

        foreach ($sections as $className) {
            if (is_object($className)) {
                $instances[] = $className;
                continue;
            }
            if (!$this->isValidSectionClass($className)) {
                continue;
            }

            $instance = $this->getSectionInstance($container, $className);
            if ($instance !== null) {
                $instances[] = $instance;
            }
        }

        return $instances;
    }

    private function isValidSectionClass(string $className): bool
    {
        return class_exists($className) && is_subclass_of($className, HtmlSectionInterface::class);
    }

    private function getSectionInstance(ContainerInterface $container, string $className): ?object
    {
        try {
            return $container->has($className)
                ? $container->get($className)
                : new $className();
        } catch (Throwable $e) {
            // Log error if you have a logger
            return null;
        }
    }

    private function buildSectionsFromConfigs(): array
    {
        $sectionConfigs = $this->config->getSectionConfigs();

        if (empty($sectionConfigs)) {
            return [];
        }

        $sections = [];

        foreach ($sectionConfigs as $config) {
            $section = $this->createSectionFromConfig($config);
            if ($section !== null) {
                $sections[] = $section;
            }
        }

        return $sections;
    }

    private function createSectionFromConfig(object $config): ?object
    {
        $mapping = $this->findMappingForConfig($config);

        if ($mapping === null) {
            return null;
        }

        $prototype = $this->getPrototype($mapping->value);

        if ($prototype === null) {
            return null;
        }

        $methodName = $mapping->getConfigMethod();

        if (!method_exists($prototype, $methodName)) {
            return null;
        }

        return $prototype->$methodName($config);
    }

    private function findMappingForConfig(object $config): ?SectionMapping
    {
        foreach (SectionMapping::cases() as $mapping) {
            $configClass = $mapping->getConfigClass();
            if ($config instanceof $configClass) {
                return $mapping;
            }
        }
        return null;
    }

    private function getPrototype(string $className): ?object
    {
        if (!isset($this->prototypeInstances[$className])) {
            $prototype = $this->createPrototype($className);

            if ($prototype === null) {
                return null;
            }

            $this->prototypeInstances[$className] = $prototype;
        }

        return clone $this->prototypeInstances[$className];
    }

    private function createPrototype(string $className): ?object
    {
        $container = ContainerRegistry::getContainer();

        if (!$container->has($className)) {
            return null;
        }

        return $container->get($className);
    }
}