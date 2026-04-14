<?php

declare(strict_types=1);

class PDOConnection implements DatabaseConnectionInterface
{
    /**
     * @var array
     */
    private array $credentials;

    /**
     * @var PDO
     */
    private ?PDO $con = null;

    /**
     * @param DatabaseEnvironmentConfig $env
     *
     * @throws DataMapperInvalidArgumentException
     *
     * @return void
     */
    public function __construct(DatabaseEnvironmentConfig $env)
    {
        $this->credentials = $env->getCredentials();
    }

    /**
     * @throws DatabaseConnexionExceptions
     *
     * @return PDO
     */
    public function open(): PDO
    {
        // Set Options
        $options = [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => true,
            PDO::MYSQL_ATTR_FOUND_ROWS => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_CASE => PDO::CASE_NATURAL,
            PDO::ATTR_ORACLE_NULLS => PDO::NULL_EMPTY_STRING,
        ];
        if (!isset($this->con) || null === $this->con) {
            try {
                $this->con = new PDO($this->credentials['dsn'], $this->credentials['dbUser'], $this->credentials['dbPass'], $options);
            } catch (PDOException $e) {
                throw new DatabaseConnexionExceptions($e->getMessage(), (int) $e->getCode());
            }
        }
        return $this->con;
    }

    /**
     * @return void
     */
    public function close(): void
    {
        $this->con = null;
    }

    /**
     * Get the value of con.
     *
     * @return  PDO
     */
    public function getConnexion(): PDO
    {
        return $this->con;
    }

    public function quote(string $value): string
    {
        if ($this->con === null) {
            $this->open();
        }
        return $this->con->quote($value);
    }

    public function beginTransaction(): bool
    {
        if ($this->con === null) {
            $this->open();
        }
        return $this->con->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->con->commit();
    }

    public function rollback(): bool
    {
        return $this->con->rollback();
    }

    public function lastInsertId(string|null $name = null): string|false
    {
        return $this->con->lastInsertId($name);
    }

    public function getAttribute(int $attribute): mixed
    {
        if ($this->con === null) {
            $this->open();
        }

        try {
            return $this->con->getAttribute($attribute);
        } catch (PDOException $e) {
            return $this->getFallbackAttribute($attribute);
        }
    }

    public function getDriverName(): string
    {
        return (string) $this->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    public function getServerVersion(): string
    {
        return (string) $this->getAttribute(PDO::ATTR_SERVER_VERSION);
    }

    public function isMariaDB(): bool
    {
        $version = $this->getServerVersion();
        return stripos($version, 'mariadb') !== false ||
               stripos($version, 'maria') !== false;
    }

    public function getDatabaseVersion(): float
    {
        $version = $this->getServerVersion();

        // Extract version number (e.g., "10.3.32-MariaDB" -> 10.3)
        if (preg_match('/(\d+\.\d+)/', $version, $matches)) {
            return (float) $matches[1];
        }

        return 0.0;
    }

    private function getFallbackAttribute(int $attribute): mixed
    {
        return match($attribute) {
            PDO::ATTR_DRIVER_NAME => 'mysql', // Default assumption
            PDO::ATTR_SERVER_VERSION => '10.0.0', // Conservative version
            PDO::ATTR_SERVER_INFO => '',
            PDO::ATTR_CONNECTION_STATUS => 'Connection OK',
            PDO::ATTR_CLIENT_VERSION => '',
            default => throw new PDOException('Attribute not supported'),
        };
    }
}