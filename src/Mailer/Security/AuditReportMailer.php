<?php

declare(strict_types=1);

namespace App\Mailer\Security;

use App\Mailer\Mailer;

final class AuditReportMailer
{
    private const MAIL_DEST = 'ainemehdi6@gmail.com';

    public function __construct(private readonly Mailer $mailer)
    {
    }

    public function SendResultMail(array $advisories): void
    {

        $this->mailer->sendTwigTemplateBasedEmail(
            self::MAIL_DEST,
            'Vérification des vulnérabilités des packages',
            'mails/security/audit_report.html.twig',
            [
                'advisories' => $advisories,
            ]
        );
    }
}
