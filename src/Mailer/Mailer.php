<?php

declare(strict_types=1);

namespace App\Mailer;

use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class Mailer
{
    public function __construct(
        protected readonly MailerInterface $mailer,
        protected readonly LoggerInterface $logger,
        protected string $mailNoReply,
    ) {
    }

    public function sendTwigTemplateBasedEmail(
        array|string $mailTo,
        string $subject,
        string $twigTemplate,
        array $vars,
    ): void {
        $mailTo = (array) $mailTo;

        $message = (new TemplatedEmail())
            ->subject($subject)
            ->from($this->mailNoReply)
            ->to(...$mailTo)
            ->htmlTemplate($twigTemplate)
            ->context($vars)
        ;

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $e) {
            dump($e->getMessage());
            $this->logger->error("'Une erreur est survenue lors de l'envoi' d'un mail", [
                'subject' => $subject,
                'to' => $mailTo,
                'cause' => $e->getMessage(),
            ]);
        }
    }
}
