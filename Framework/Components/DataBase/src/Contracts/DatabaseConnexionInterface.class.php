<?php

declare(strict_types=1);

interface DatabaseConnexionInterface
{
    /**
     * DataBase open
     * -----------------------------------------------.
     * @return PDO
     */
    public function open(): PDO;

    /**
     * Data Base close
     * ------------------------------------------------.
     * @return void
     */
    public function close():void;
}