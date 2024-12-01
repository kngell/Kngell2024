<?php

declare(strict_types=1);
abstract class AbstractHTMLPage
{
    use PagesTraits;

    protected self $parent;
    protected int $level;
    protected CollectionInterface $paths;

    public function __construct(?TemplatePathsInterface $paths = null)
    {
        if (! is_null($paths)) {
            $this->paths = $paths->Paths();
        }
    }

    abstract public function display() : array;

    /**
     * Get the value of parent.
     */
    public function getParent(): self
    {
        return $this->parent;
    }

    /**
     * Set the value of parent.
     */
    public function setParent(self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get the value of level.
     */
    public function getLevel(): int
    {
        return $this->level;
    }

    /**
     * Set the value of level.
     */
    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    protected function isFileexists(string $file) : bool
    {
        if (! file_exists($file)) {
            throw new BaseException('File does not exist!', 1);
        }
        return true;
    }

    protected function getTemplate(string $path) : string
    {
        $this->isFileexists($this->paths->offsetGet($path));
        return file_get_contents($this->paths->offsetGet($path));
    }

    protected function media(object $obj, ?string $defaultMadia = null, bool $string = true) : string|array
    {
        if (isset($obj->media) && ! is_null($obj->media)) {
            $media = ! is_array($obj->media) ? unserialize($obj->media) : $obj->media;
            if (is_array($media) && count($media) > 0) {
                $all_media = [];
                foreach ($media as $med) {
                    $all_media[] = str_starts_with($med, IMG) ? $med : ImageManager::asset_img($med);
                }
                return $string ? $all_media[0] : $all_media;
            }
        }
        if ($defaultMadia !== null) {
            return ImageManager::asset_img($defaultMadia);
        }
        return '';
    }
}