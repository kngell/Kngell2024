<?php

declare(strict_types=1);
class BasicEmailListener implements EventListenerInterface
{
    private MailerFacade $mailer;

    public function __construct(MailerFacade $mailer)
    {
        $this->mailer = $mailer;
    }

    public function update(EventInterface $event): ?object
    {
        $params = $event->getParams();
        $mail = $this->mailer
            ->charset('utf-8')
            ->basicMail(
                $params['subject'],
                ['email' => 'admin@kngell.com'],
                $params['email'],
                $params['html']
            );
        if ($mail) {
            $std = new stdClass();
            $std->result = $mail;
            return $event->setResults($std);
        }
        return new NullEvent();
    }
}
