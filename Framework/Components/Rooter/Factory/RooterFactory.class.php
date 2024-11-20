<?php

declare(strict_types=1);

class RooterFactory
{
    /**
     * Main constructor.
     */
    public function __construct(private RooterInterface $rooter)
    {
    }

    public function create() : RooterInterface
    {
        if (empty($this->routes)) {
            throw new BaseNoValueException("There are one or more empty arguments. In order to continue, please ensure your <code>routes.yaml</code> has your defined routes and you are passing the correct variable ie 'QUERY_STRING'");
        }
        if (! $this->rooter instanceof RooterInterface) {
            throw new BaseUnexpectedValueException(get_class($this->rooter) . ' is not a valid rooter Object!');
        }

        return $this->rooter;
    }
}