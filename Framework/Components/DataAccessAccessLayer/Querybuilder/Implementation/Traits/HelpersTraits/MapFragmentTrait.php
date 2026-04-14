<?php

declare(strict_types=1);

trait MapFragmentTrait
{
    public function getMapFragments(array $map, array $methods, string $tableMathod): array
    {
        $fragments = [$this->getTableFromMap($map, $tableMathod)];
        foreach ($methods as $method) {
            $fragments[] = $this->getPayloadData($map, $method);
        }

        return $fragments;
    }

    private function getTableFromMap(array $map, string $method): ?string
    {
        if (!isset($map[$method])) {
            return null;
        }
        $payload = $map[$method];
        if (is_string($payload)) {
            return $payload;
        }
        if (!$payload instanceof SqlDataPayloadInterface) {
            return null;
        }
        $data = $map[$method]->getData();
        return $data['table'];
    }

    private function getPayloadData(array $map, string $method): ?array
    {
        if (!isset($map[$method])) {
            return null;
        }
        if ($map[$method] instanceof SqlDataPayloadInterface) {
            $data = $map[$method]->getData();
            if (empty($data)) {
                return null;
            }
            return $data;
        }
        return ArrayUtils::isObjectList($map[$method]) ? $map[$method] : null;
    }
}
