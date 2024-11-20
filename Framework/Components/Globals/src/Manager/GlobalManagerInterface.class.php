<?php

declare(strict_types=1);

interface GlobalManagerInterface_old
{
    /**
     * Set globals Variables
     * --------------------------------------------------------------------------------------------------.
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set(string $key, mixed $value) :void;

    /**
     * Get Globals
     * --------------------------------------------------------------------------------------------------.
     * @param string $key
     * @return mixed
     */
    public static function get(string $key) : mixed;
}