<?php

declare(strict_types=1);

interface SuperGlobalsInterface
{
    /**
     * Get Variable.
     *
     * @param string|null $key
     * @return mixed
     */
    public function _Get(?string $key = null): mixed;

    /**
     * Post Variable.
     *
     * @param string|null $key
     * @return mixed
     */
    public function _Post(?string $key = null): mixed;

    /**
     * Cookie Variable.
     *
     * @param string|null $key
     * @return mixed
     */
    public function _Cookies(?string $key = null): mixed;

    /**
     * File Variable.
     *
     * @param string|null $key
     * @return mixed
     */
    public function _Files(?string $key = null): mixed;

    /**
     * Server Variable.
     *
     * @param string|null $key
     * @return mixed
     */
    public function _Server(?string $key): mixed;
}