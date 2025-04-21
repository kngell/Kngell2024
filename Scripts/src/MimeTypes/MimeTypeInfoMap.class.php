<?php

declare(strict_types=1);

class MimeTypeInfoMap
{
    /**
     * @var MimeTypesInfo[]
     */
    private array $mimeTypeInfos = [];

    public function addType(string $type) : void
    {
        if ($this->getByType($type) !== null) {
            return;
        }
        $this->mimeTypeInfos[] = new MimeTypesInfo([], [$type]);
    }

    public function addAliasType(string $alias, string $type) : void
    {
        $existing = $this->getByType($type);
        if ($existing === null) {
            return;
        }
        $existing->addContentType($alias);
    }

    public function addExtensionToType(string $extension, string $type) : void
    {
        $existing = $this->getByType($type);
        if ($existing === null) {
            $this->mimeTypeInfos[] = new MimeTypesInfo([$extension], [$type]);
        } else {
            $existing->addExtension($extension);
        }
    }

    public function getByType(string $type) : MimeTypesInfo|null
    {
        foreach ($this->mimeTypeInfos as $mimeTypeInfos) {
            if ($mimeTypeInfos->hasContentType($type)) {
                return $mimeTypeInfos;
            }
        }
        return null;
    }

    /**
     * Get the value of mimeTypeInfos.
     *
     * @return  MimeTypesInfo[]
     */
    public function getMimeTypeInfos()
    {
        return $this->mimeTypeInfos;
    }
}
