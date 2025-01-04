<?php

declare(strict_types=1);
abstract class AbstractMailer implements MailerInterface
{
    /**
     * Checks whether we are passing in a value that is not empty else we will
     * throw a nice exception.
     *
     * @param mixed $value
     * @return void
     */
    public function isValid(mixed $value) : void
    {
        if (empty($value)) {
            throw new MailerInvalidArgumentException('Invalid or empty argument provided. Please address this.');
        }
    }
}
