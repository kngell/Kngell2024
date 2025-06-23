<?php

declare(strict_types=1);

final class GlobalManager implements GlobalManagerInterface
{
    // /** @var GlobalManagerInterface */
    // protected static $instance;

    // private function __construct()
    // {
    // }

    // /**
    //  * Get container instance
    //  * ===============================================.
    //  * @return mixed
    //  */
    // public static function getInstance() : mixed
    // {
    //     if (!isset(static::$instance)) {
    //         static::$instance = new static();
    //     }
    //     return static::$instance;
    // }

    /**
     * @inheritdoc
     *
     * @param string $name
     * @param mixed $context
     * @return void
     */
    public static function set(string $name, mixed $context): void
    {
        if ($name !== '') {
            $GLOBALS[$name] = $context;
        }
    }

    /**
     * @inheritdoc
     *
     * @param string $name
     * @return mixed
     * @throws GlobalManagerException
     */
    public static function get(string $name): mixed
    {
        self::isGlobalValid($name);
        return $GLOBALS[$name];
    }

    /**
     * Check whether the global name is set else throw an exception.
     *
     * @param string $name
     * @return void
     * @throws GlobalManagerException
     */
    protected static function isGlobalValid(string $name): void
    {
        if (!isset($GLOBALS[$name]) || empty($name)) {
            throw new GlobalManagerException("Invalid global. Please ensure you've set the global state for " . $name . ' And the feature is set to true from your pubic/index.php file.');
        }
    }
}