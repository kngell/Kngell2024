<?php

declare(strict_types=1);

#[Service]
readonly class Service1
{
    public function __construct(
        private CommonInterface $common,
        #[Qualifier(name: 'CommonWithName')]
        private CommonInterface $commonWithName
    ) {
    }

    /**
     * Get the value of common.
     */
    public function getCommon()
    {
        return $this->common;
    }

    /**
     * Get the value of commonWithName.
     */
    public function getCommonWithName()
    {
        return $this->commonWithName;
    }
}