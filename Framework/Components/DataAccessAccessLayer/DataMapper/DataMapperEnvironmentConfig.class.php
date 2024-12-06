<?php

declare(strict_types=1);
class DataMapperEnvironmentConfig
{
    private array|Closure $credentials = [];
    private string $driver;

    public function __construct(array|Closure $credentials, string $driver)
    {
        $this->credentials = $credentials;
        $this->driver = $driver;
    }

    public function getCredentials() : array
    {
        if ($this->credentials instanceof Closure) {
            $this->credentials = $this->credentials->__invoke();
        }
        $this->isCredentialsValid($this->driver);
        $connexionArray = [];
        foreach ($this->credentials as $credentials) {
            if (array_key_exists($this->driver, $credentials)) {
                $connexionArray = $credentials[$this->driver];
            }
        }

        return $connexionArray;
    }

    /**
     * Get the value of driver.
     *
     * @return string
     */
    public function getDriver(): string
    {
        return $this->driver;
    }

    /**
     * --------------------------------------------
     * Check for Valid credentials.
     *@param string driver
     * @return array
     */
    private function isCredentialsValid(string $driver)
    {
        if (empty($driver) && ! is_string($driver)) {
            throw new DataMapperInvalidArgumentException('Invalid Argument! This is missing or invalid Data type');
        }
        if (! is_array($this->credentials)) {
            throw new DataMapperInvalidArgumentException('Invalid Credentials!');
        }
        if (! in_array('driver', array_keys($this->credentials))) {
            throw new DataMapperInvalidArgumentException('Invalid or unsupported driver');
        }
    }
}