<?php

declare(strict_types=1);

class ParameterManager
{
    private array $parameters = [];
    private array $bindArr = [];
    private array $tableAlias = [];
    private array $aliasCheck = [];
    private array $logicalToPhysicalMap = [];

    public function addParameter(string $name, mixed $value): void
    {
        if (array_key_exists($name, $this->parameters)) {
            throw new RuntimeException("Parameter '{$name}' already exists");
        }
        $this->parameters[$name] = $value;
    }

    public function generateUniqueName(string $baseName): string
    {
        $counter = 1;
        $name = $baseName;

        while (array_key_exists($name, $this->parameters)) {
            $name = $baseName . '_' . $counter;
            $counter++;
        }

        return $name;
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getBindArr(): array
    {
        return $this->bindArr;
    }

    public function setBindArr(array $bindArr): void
    {
        $this->bindArr = $bindArr;
    }

    public function getTableAlias(): array
    {
        return $this->tableAlias;
    }

    public function setTableAlias(array $tableAlias): void
    {
        $this->tableAlias = $tableAlias;
    }

    public function getAliasCheck(): array
    {
        return $this->aliasCheck;
    }

    public function setAliasCheck(array $aliasCheck): void
    {
        $this->aliasCheck = $aliasCheck;
    }

    public function merge(self $other): self
    {
        return new self(
            $this->safeArrayMerge($this->parameters, $other->getParameters()),
            $this->safeArrayMerge($this->bindArr, $other->getBindArr()),
            $this->safeArrayMerge($this->tableAlias, $other->getTableAlias()),
            $this->safeArrayMerge($this->aliasCheck, $this->addAliasCheck($other->getAliasCheck())),
            $this->safeArrayMerge($this->logicalToPhysicalMap, $other->getLogicalToPhysicalMap()),
        );
    }

    public function mergeArrays(array $parameters, array $bindArr, array $tableAlias, array $aliasCheck): void
    {
        $this->parameters = $this->safeArrayMerge($this->parameters, $parameters);
        $this->bindArr = $this->safeArrayMerge($this->bindArr, $bindArr);
        $this->tableAlias = $this->safeArrayMerge($this->tableAlias, $tableAlias);
        $this->aliasCheck = $this->safeArrayMerge($this->aliasCheck, $this->addAliasCheck($aliasCheck));
    }

    public function getAllParams(): array
    {
        return [
            $this->parameters, $this->bindArr, $this->tableAlias, $this->aliasCheck,
        ];
    }

    public function clear(): void
    {
        $this->parameters = [];
        $this->bindArr = [];
        $this->tableAlias = [];
        $this->aliasCheck = [];
    }

    /**
     * @return array
     */
    public function getLogicalToPhysicalMap(): array
    {
        return $this->logicalToPhysicalMap;
    }

    /**
     * @param array $logicalToPhysicalMap
     *
     * @return ParameterManager
     */
    public function setLogicalToPhysicalMap(array $logicalToPhysicalMap): ParameterManager
    {
        $this->logicalToPhysicalMap = $logicalToPhysicalMap;

        return $this;
    }

    private function safeArrayMerge(array $array1, array $array2): array
    {
        // Ensure both are arrays
        $array1 = is_array($array1) ? $array1 : [];
        $array2 = is_array($array2) ? $array2 : [];

        if (empty($array2)) {
            return $array1;
        }

        // Check for potential issues
        $totalSize = count($array1) + count($array2);
        if ($totalSize > 10000) {
            throw new RuntimeException(
                "Large array merge detected: {$totalSize} elements. " .
                'Array1: ' . count($array1) . ' elements, ' .
                'Array2: ' . count($array2) . ' elements',
            );
        }

        try {
            return array_merge($array1, $array2);
        } catch (Throwable $e) {
            // Fallback: manual merge
            error_log('Array merge failed, using fallback: ' . $e->getMessage());
            foreach ($array2 as $key => $value) {
                $array1[$key] = $value;
            }
            return $array1;
        }
    }

    private function addAliasCheck(array $aliascheck): array
    {
        $aliasArr = [];
        foreach ($aliascheck as $key => $alias) {
            if (!in_array($alias, $this->aliasCheck)) {
                $aliasArr[] = $alias;
            }
        }
        return $aliasArr;
    }
}