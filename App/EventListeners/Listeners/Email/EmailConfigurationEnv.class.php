<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

class EmailConfigurationEnv
{
    // const SETTINGS = [
    //     'SMTPDebug' => 0,
    //     'SMTPAuth' => true,
    //     'SMTPSecure' => PHPMailer::ENCRYPTION_SMTPS,
    //     'Username' => SMTP_USERNAME,
    //     'Password' => SMTP_PASSWORD,
    //     'Host' => SMTP_HOST,
    //     'Port' => SMTP_PORT,
    // ];
    private string $to;
    private array $from;
    private string $subject;
    private string $text;
    private string $emailClass;

    /**
     * Get the value of subject.
     */
    public function getSubject() : string
    {
        return $this->subject;
    }

    /**
     * Set the value of subject.
     *
     * @return  self
     */
    public function setSubject($subject) : self
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the value of from.
     */
    public function getFrom() : array
    {
        return $this->from;
    }

    /**
     * Set the value of from.
     *
     * @return  self
     */
    public function setFrom(null|string $from = '', null|string $name = '') : self
    {
        $from !== '' ? $this->from['email'] = $from : '';
        $name !== '' ? $this->from['name'] = $name : '';

        return $this;
    }

    /**
     * Get the value of emailClass.
     */
    public function getEmailClass() : string
    {
        return $this->emailClass;
    }

    /**
     * Set the value of emailClass.
     *
     * @return  self
     */
    public function setEmailClass(string $emailClass) : self
    {
        $this->emailClass = $emailClass;

        return $this;
    }
}