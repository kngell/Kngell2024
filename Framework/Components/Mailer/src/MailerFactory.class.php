<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

class MailerFactory
{
    /** @var array|null */
    protected ?array $settings = null;

    /**
     * Construct.
     * @param array|null $settings
     */
    public function __construct(?array $settings = null)
    {
        $this->settings = $settings;
    }

    /**
     * Create method which creates the Mailer object and inject the relevant arguments.
     *
     * @param string $transportString
     * @return MailerInterface
     * @throws MailerInvalidArgumentException
     */
    public function create(string $transportString) : MailerInterface
    {
        $transporterObject = new $transportString(true);
        if (! $transporterObject) {
            throw new MailerInvalidArgumentException($transportString . ' is not a valid mailer object');
        }

        return App::diGet(MailerInterface::class, [
            'transporterObject' => $transporterObject,
            'settings' => $this->settings,
            'dotEnvString' => Dotenv::class,
        ]);
    }
}