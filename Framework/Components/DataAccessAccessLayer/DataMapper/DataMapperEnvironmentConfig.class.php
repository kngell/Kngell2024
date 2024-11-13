<?php

declare(strict_types=1);
class DataMapperEnvironmentConfig
{
    /**
     * --------------------------------------------------------------------------------------------------
     * Cedentials.
     * @var array
     */
    private array $credentials = [];

    public function __construct(array $credentials)
    {
        $this->credentials = $credentials;
    }

    // public function setCredentials(array $credentials) : self
    // {
    //     $this->credentials = $credentials;
    //     return $this;
    // }

    /**
     * --------------------------------------------------------------------------------------------------
     * Get user data base credentials.
     *@param string $driver
     * @return array
     */
    public function getCredentials(string $driver) : array
    {
        $this->isCredentialsValid($driver);
        $connexionArray = [];
        foreach ($this->credentials as $credentials) {
            if (array_key_exists($driver, $credentials)) {
                $connexionArray = $credentials[$driver];
            }
        }

        return $connexionArray;
    }

    /**
     * --------------------------------------------------------------------------------------------------
     * Check for Valid credentials.
     *@param string driver
     * @return array
     */
    private function isCredentialsValid(string $driver)
    {
        if (empty($driver) && !is_string($driver)) {
            throw new DataMapperInvalidArgumentException('Invalid Argument! This is missing or invalid Data type');
        }
        if (!is_array($this->credentials)) {
            throw new DataMapperInvalidArgumentException('Invalid Credentials!');
        }
        if (!in_array('driver', array_keys($this->credentials))) {
            throw new DataMapperInvalidArgumentException('Invalid or unsupported driver');
        }
    }
}
