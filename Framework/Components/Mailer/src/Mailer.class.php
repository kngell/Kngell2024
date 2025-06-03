<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

class Mailer extends AbstractMailer
{
    /** @var PHPMailer */
    protected PHPMailer $transporterObject;
    /** @var array */
    protected array $options = [];
    /** @var array */
    protected array $setting = [];

    /**
     * Undocumented function.
     *
     * @param PHPMailer $transporterObject
     * @param array|null $settings
     */
    public function __construct(PHPMailer $transporterObject, ?array $settings = null)
    {
        $this->transporterObject = $transporterObject;
        if (is_array($settings) && count($settings) > 0) {
            $this->setSettings($settings);
        } else {
            $this->setSettings(null);
        }
    }

    /**
     * @inheritdoc
     * @param string $subject
     * @return self
     * @throws MailerInvalidArgumentException
     */
    public function subject(string $subject) : self
    {
        $this->isValid($subject);
        $this->transporterObject->isHTML(true);
        $this->transporterObject->Subject = $subject;

        return $this;
    }

    /**
     * @inheritdoc
     * @param array $from
     * @return self
     * @throws MailerInvalidArgumentException
     */
    public function from(array $from = []) : self
    {
        $this->isValid($from['email']);
        $this->transporterObject->setFrom($from['email'], (isset($from['name']) && ! empty($from['name'])) ? $from['name'] : '');

        return $this;
    }

    public function charset(string $hset) : self
    {
        $this->transporterObject->CharSet = $hset;

        return $this;
    }

    /**
     * @inheritdoc
     * @param string $message
     * @return self
     * @throws MailerInvalidArgumentException
     */
    public function body(string $message, $externalSource = null, $externalSourcePath = null) : self
    {
        $this->isValid($message);
        $this->transporterObject->Body = $message;
        $this->transporterObject->AltBody = $message;
        if ($externalSource != null) {
            $this->transporterObject->msgHTML(file_get_contents($externalSource), $externalSourcePath);
        }

        return $this;
    }

    /**
     * @inheritdoc
     * @param mixed|null $args
     * @return self
     */
    public function address(mixed $args = null) : self
    {
        if (is_array($args) && $args != null) {
            foreach ($args as $name => $address) {
                $this->transporterObject->addAddress($address, $name);
            }
        } else {
            $this->transporterObject->addAddress($args);
        }

        return $this;
    }

    /**
     * @inheritdoc
     * @param string $from
     * @param string|null $name
     * @return self
     * @throws MailerInvalidArgumentException
     */
    public function toReply(string $from, ?string $name = null) : self
    {
        $this->isValid($from);
        $this->transporterObject->addReplyTo($from, ($name !== null) ? $name : null);

        return $this;
    }

    /**
     * @inheritdoc
     * @param string $cc
     * @return self
     * @throws MailerInvalidArgumentException
     */
    public function cc(string $cc) : self
    {
        $this->isValid($cc);
        $this->transporterObject->addCC($cc);

        return $this;
    }

    /**
     * @inheritdoc
     * @param string $bcc
     * @return self
     * @throws MailerInvalidArgumentException
     */
    public function bcc(string $bcc) : self
    {
        $this->isValid($bcc);
        $this->transporterObject->addBCC($bcc);

        return $this;
    }

    /**
     * @inheritdoc
     * @param mixed|null $args
     * @return self
     * @throws MailerInvalidArgumentException
     */
    public function attachments(mixed $args = null) : self
    {
        if (is_array($args)) {
            foreach ($args as $name => $attachment) {
                $this->transporterObject->addAttachment($attachment, $name);
            }
        } else {
            $this->transporterObject->addAttachment($args);
        }

        return $this;
    }

    /**
     * @return bool|string
     * @throws MailerException
     */
    public function send(): bool|string
    {
        try {
            if (! $rs = $this->transporterObject->send()) {
                return 'Mail Error: ' . $this->transporterObject->ErrorInfo;
            }
            return $rs;
        } catch (MailerException $exception) {
            throw $exception;
        }
        return false;
    }

    /**
     * Loop the user settings from the constructor method and assign each key
     * to its value. Ensure all keys and value is set and defined.
     *
     * @param array|null $settings
     * @return void
     */
    protected function getSettings(?array $settings = null) : void
    {
        if (is_array($settings)) {
            foreach ($settings as $key => $value) {
                if (isset($key) && $key !== '') {
                    $this->setting[$key] = ($value ?? '');
                }
            }
        }
    }

    /**
     * Returns the Mail server configuration settings from either the constructor or
     * an external $_ENV file within the application root.
     * Please note. You cannot use both at the same time. We can only use the ENV file
     * or the pass an array of server settings through the MailerFacade constructor.
     *
     * @param string $param
     * @return mixed
     */
    protected function defaultOrEnv(string $param): mixed
    {
        $this->isValid($param);
        // if (isset($this->setting[$param]) && $this->setting[$param] !== '' && ! isset($_ENV[$param])) {
        //     return $this->setting[$param];
        // }
        if (isset($_ENV[$param]) && empty($this->setting[$param])) {
            return $_ENV[$param];
        }
        return $this->setting[$param];
    }

    /**
     * Bind the environment or user defined settings the Mailer object. Settings are
     * provided by either the constructor method or and ENV file within the application
     * root of the project using this library.
     *
     * SMTP::DEBUG_OFF (0): Disable debugging (you can also leave this out completely, 0 is the default).
     * SMTP::DEBUG_CLIENT (1): Output messages sent by the client.
     * SMTP::DEBUG_SERVER (2): as 1, plus responses received from the server (this is the most useful setting).
     * SMTP::DEBUG_CONNECTION (3): as 2, plus more information about the initial connection - this level can help diagnose STARTTLS failures.
     * SMTP::DEBUG_LOWLEVEL (4): as 3, plus even lower-level information, very verbose, don't use for debugging SMTP, only low-level problems.
     *
     * @param array|null $settings
     * @return void
     */
    protected function setSettings(?array $settings = null) : void
    {
        $this->getSettings($settings);

        $this->transporterObject->SMTPDebug = $this->defaultOrEnv('SMTPDebug');
        $this->transporterObject->isSMTP();
        $this->transporterObject->Host = $this->defaultOrEnv('Host');
        $this->transporterObject->SMTPAuth = $this->defaultOrEnv('SMTPAuth');
        $this->transporterObject->Username = $this->defaultOrEnv('Username');
        $this->transporterObject->Password = $this->defaultOrEnv('Password');
        $this->transporterObject->SMTPSecure = $this->defaultOrEnv('SMTPSecure');
        $this->transporterObject->Port = $this->defaultOrEnv('Port');
    }
}