<?php

declare(strict_types=1);

class BaseNoValueException extends BaseLogicException
{
    /**
     * Custom framework exception which is thrown when calling an empty argument.
     *
     * @param string $message
     * @param int $code
     * @param BaseLogicException $previous
     * @throws LogicException
     */
    public function __construct(string $message, int $code = 0, ?BaseLogicException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
