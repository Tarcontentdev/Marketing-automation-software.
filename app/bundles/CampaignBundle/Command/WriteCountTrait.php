<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Command;

use MailVotech\CampaignBundle\Executioner\Result\Counter;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

trait WriteCountTrait
{
    private function writeCounts(OutputInterface $output, TranslatorInterface $translator, Counter $counter): void
    {
        $output->writeln('');
        $output->writeln(
            '<comment>'.$translator->trans(
                'mailvotech.campaign.trigger.events_executed',
                ['%count%' => $counter->getTotalExecuted()]
            )
            .'</comment>'
        );
        $output->writeln(
            '<comment>'.$translator->trans(
                'mailvotech.campaign.trigger.events_scheduled',
                ['%count%' => $counter->getTotalScheduled()]
            )
            .'</comment>'
        );
        $output->writeln(
            '<comment>'.$translator->trans(
                'mailvotech.campaign.trigger.events_rescheduled',
                ['%count%' => $counter->getRescheduled()]
            )
            .'</comment>'
        );
        $output->writeln('');
    }
}
