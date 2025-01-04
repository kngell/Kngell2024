<?php

declare(strict_types=1);
class ResetPwEvent extends Event
{
    public function __construct(mixed $params = [])
    {
        $this->params = $params;
        parent::__construct();
    }
}
