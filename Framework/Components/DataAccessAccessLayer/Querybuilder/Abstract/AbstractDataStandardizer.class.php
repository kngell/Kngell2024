<?php

declare(strict_types=1);

abstract class AbstractDataStandardizer implements DataStandardizerInterface
{
    protected array $map = [];
    protected ?string $method = null;

    abstract public function standardize(array $data): SelectPayload|InsertPayload|UpdatePayload|SqlGenericDataPayload|OnPayload;

    abstract public function getContext(): string;

    public function setMethod(?string $method): static
    {
        $this->method = $method;
        return $this;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMap(array $map): static
    {
        $this->map = $map;
        return $this;
    }

    protected function getRealData(array $data): array
    {
        if (count($data) === 1 && isset($data[0]) && is_array($data[0])) {
            return $this->getRealData($data[0]);
        }
        return $data;
    }

    protected function toAssoc(array $data): array
    {
        if (!$this->couldBeKeyValue($data)) {
            throw new InvalidArgumentException('Invalid key-value pair count');
        }

        return $this->convertKeyValueList($data);
    }

    protected function fromPairListToAssoc(array $pairs): array
    {
        $result = [];
        foreach ($pairs as $item) {
            if (ArrayUtils::isAssoc($item)) {
                $key = array_key_first($item);
                $result[$key] = $item[$key];
            } else {
                $result[$item[0]] = $item[1];
            }
        }
        return $result;
    }

    protected function isPairList(array $data): bool
    {
        foreach ($data as $item) {
            if (!is_array($item) || empty($item)) {
                return false;
            }

            if (ArrayUtils::isAssoc($item)) {
                if (count($item) !== 1) {
                    return false;
                }
                continue;
            }

            if (count($item) !== 2 || !is_string($item[0])) {
                return false;
            }
        }

        return true;
    }

    protected function standardizeConditions(array $data): SqlDataPayloadInterface
    {
        if (empty($data)) {
            throw new BadQueryArgumentException($this->method . ' condition requires at least one condition');
        }
        return new SqlGenericDataPayload($data, $this->method);
    }

    private function couldBeKeyValue(array $data): bool
    {
        return ArrayUtils::isStringList($data) &&
               count($data) % 2 === 0 &&
               count($data) >= 2;
    }

    private function convertKeyValueList(array $keyValueList): array
    {
        $result = [];
        for ($i = 0; $i < count($keyValueList); $i += 2) {
            if (!is_string($keyValueList[$i])) {
                throw new InvalidArgumentException('Column name must be string');
            }
            $result[$keyValueList[$i]] = $keyValueList[$i + 1];
        }
        return $result;
    }
}