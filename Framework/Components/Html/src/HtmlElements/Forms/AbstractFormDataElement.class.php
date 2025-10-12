<?php

declare(strict_types=1);

abstract class AbstractFormDataElement extends AbstractHtmlComponent
{
    /**
     * @param string $name
     *
     * @return self
     */
    public function name(string $name): self
    {
        return $this;
    }

    /**
     * @param mixed $value
     *
     * @return self
     */
    public function value(mixed $value): self
    {
        return $this;
    }
}