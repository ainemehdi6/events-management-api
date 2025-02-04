<?php

namespace App\Command\Security;

use App\Mailer\Security\AuditReportMailer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SecurityCheckerCommand extends Command
{
    public function __construct(private readonly AuditReportMailer $mailer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('security-checker')
            ->setDescription('Verifie les vulnerabilites des packages installés')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Debut du lancement de la commande');
        $response = shell_exec('composer audit -f json');

        $data = json_decode($response, true);
        $advisories = [];
        foreach ($data['advisories'] as $packageAdvisories) {
            $advisories = array_merge($advisories, $packageAdvisories);
        }

        $this->mailer->SendResultMail($advisories);

        $output->writeln('Commandes lancées');

        return self::SUCCESS;
    }
}
