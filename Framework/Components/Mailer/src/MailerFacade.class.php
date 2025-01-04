<?php

declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

class MailerFacade
{
    /** @var MailerInterface */
    protected MailerInterface $mailer;
    private array $settings;

    public function __construct(?array $settings)
    {
        $this->settings = $settings;
        $this->mailer = App::diGet(MailerFactory::class, [
            'settings' => $settings,
        ])->create(PHPMailer::class);
    }

    /**
     * Send Basic Email.
     * @param string $subject
     * @param array $from
     * @param string $to
     * @param string $message
     * @return mixed
     * @throws MailerInvalidArgumentException
     * @throws MailerException
     */
    public function basicMail(string $subject, array $from, string $to, string $message): mixed
    {
        return $this->mailer
            ->subject($subject)
            ->from($from)
            ->address($to)
            ->body($message)
            ->send();
    }

    public function charset(?string $hset = null) : self
    {
        if (null !== $hset) {
            $this->mailer->charset($hset);
        }
        return $this;
    }

    /**
     * Get the value of settings.
     *
     * @return array
     */
    public function getSettings(): array
    {
        return $this->settings;
    }
}
