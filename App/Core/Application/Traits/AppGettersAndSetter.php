<?php

declare(strict_types=1);
trait AppGettersAndSetter
{
    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }
}