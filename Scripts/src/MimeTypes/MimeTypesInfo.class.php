<?php

declare(strict_types=1);

class MimeTypesInfo
{
    /**
     * @var string[]
     */
    private array $extentions;

    /**
     * @var string[]
     */
    private array $contentTypes;

    public function __construct(array $extentions = [], array $contentTypes = [])
    {
        $this->extentions = $extentions;
        $this->contentTypes = $contentTypes;
    }

    /**
     * Get the value of extentions.
     *
     * @return  string[]
     */
    public function getExtentions()
    {
        return $this->extentions;
    }

    /**
     * Get the value of contentTypes.
     *
     * @return  string[]
     */
    public function getContentTypes()
    {
        return $this->contentTypes;
    }

    public function hasExtension(string $ext): bool
    {
        return in_array(strtolower($ext), $this->extentions);
    }

    public function hasContentType(string $cType): bool
    {
        return in_array(strtolower($cType), $this->contentTypes);
    }

    public function addExtension(string $ext) : void
    {
        if ($this->hasExtension($ext)) {
            return;
        }
        $this->extentions[] = $ext;
    }

    public function addContentType(string $cType) : void
    {
        if ($this->hasContentType($cType)) {
            return;
        }
        $this->contentTypes[] = $cType;
    }
}
