<?php

declare(strict_types=1);

#[Service]
readonly class Service2
{
    public function __construct(
        private Service1 $service1,
        #[Property(name:'hello.world')]
        private string $helloWorld,
        private string|null $test1Nullable,
        private string|null $test2Nullable = null,
        private string $withDefault = 'with Default'
    ) {
    }

    /**
     * Get the value of service1.
     */
    public function getService1()
    {
        return $this->service1;
    }

    /**
     * Get the value of helloWorld.
     */
    public function getHelloWorld()
    {
        return $this->helloWorld;
    }

    /**
     * Get the value of test1Nullable.
     */
    public function getTest1Nullable()
    {
        return $this->test1Nullable;
    }

    /**
     * Get the value of test2Nullable.
     */
    public function getTest2Nullable()
    {
        return $this->test2Nullable;
    }

    /**
     * Get the value of withDefault.
     */
    public function getWithDefault()
    {
        return $this->withDefault;
    }
}