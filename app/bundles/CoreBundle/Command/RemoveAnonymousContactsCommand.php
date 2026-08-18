<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Command;

use MailVotech\CampaignBundle\Entity\LeadEventLogRepository;
use MailVotech\CampaignBundle\Entity\LeadRepository;
use MailVotech\CoreBundle\Helper\ExitCode;
use MailVotech\LeadBundle\Entity\ListLeadRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: self::COMMAND_NAME,
    description: 'Delete all anonymous contacts from segment, campaign and campaign event logs.'
)]
final class RemoveAnonymousContactsCommand extends Command
{
    /**
     * @var string
     */
    public const COMMAND_NAME = 'mailvotech:remove:anonymous_contacts';

    public function __construct(
        private readonly ListLeadRepository $listLeadRepository,
        private readonly LeadRepository $campaignLeadRepository,
        private readonly LeadEventLogRepository $campaignLeadEventLog,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $deletedRecords = $this->listLeadRepository->deleteAnonymousContacts();
        $output->writeln(sprintf('<info>%d record(s) deleted from segment leads.</info>', $deletedRecords));

        $deletedRecords = $this->campaignLeadRepository->deleteAnonymousContacts();
        $output->writeln(sprintf('<info>%d record(s) deleted from campaign leads.</info>', $deletedRecords));

        $deletedRecords = $this->campaignLeadEventLog->deleteAnonymousContacts();
        $output->writeln(sprintf('<info>%d record(s) deleted from campaign leads event logs.</info>', $deletedRecords));

        return ExitCode::SUCCESS;
    }
}
