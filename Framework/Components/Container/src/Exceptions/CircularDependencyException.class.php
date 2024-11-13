<?php

declare(strict_types=1);

use Psr\Container\ContainerExceptionInterface;

class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    /**
     * @var ServiceInjectionInfo[]
     */
    private array $leftOverServices;

    public function __construct(string $message, array $leftOverServices)
    {
        parent::__construct($message);
        $this->leftOverServices = $leftOverServices;
    }

    #[Override]
    public function __toString(): string
    {
        $servicesNotCreated = "[\n";
        foreach ($this->leftOverServices as $leftOverServices) {
            $servicesNotCreated .= "\t" . $leftOverServices->getClass()->getName() . "\n";
        }
        $servicesNotCreated .= "\n]";

        return __CLASS__ . " : [{$this->code}] {$this->message} -->bean that are not created \n{$servicesNotCreated}";
    }
}
