<?php

declare(strict_types=1);

class EmailSenderlistener implements EventListenerInterface
{
    private MailerFacade $mailer;

    public function __construct(MailerFacade $mailer)
    {
        $this->mailer = $mailer;
    }

    public function update(EventInterface $event): ?object
    {
        $settings = $this->mailer->getSettings();

        // $mail = $this->mailer->charset('utf-8')
        // ->basicMail('Test', ['email' => 'admin@kngell.com'], 'daniel.akono@kngell.com', '<h2>test Email Config</h2>');

        return $event->getObject();
    }
}
