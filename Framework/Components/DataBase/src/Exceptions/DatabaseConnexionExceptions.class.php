<?php

declare(strict_types=1);
class DatabaseConnexionExceptions extends PDOException
{
    protected $massage;
    protected $code;

    /**
     * Custom Database Exception
     * =========================================================.
     * @param string $message
     * @param int $code
     * @return void
     */
    public function __construct($message = null, $code = null)
    {
        $this->massage = $message;
        $this->code = $code;
    }
}
