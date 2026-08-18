<?php

namespace MailVotech\CampaignBundle\Command;

use MailVotech\CampaignBundle\Executioner\ContactFinder\Limiter\ContactLimiter;
use MailVotech\CampaignBundle\Executioner\InactiveExecutioner;
use MailVotech\CoreBundle\Twig\Helper\FormatterHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'mailvotech:campaigns:validate',
    description: 'Validate if a contact has been inactive for a decision and execute events if so.'
)]
final class ValidateEventCommand extends Command
{
    use WriteCountTrait;

    public function __construct(
        private InactiveExecutioner $inactiveExecution,
        private TranslatorInterface $translator,
        private FormatterHelper $formatterHelper,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption(
                '--decision-id',
                null,
                InputOption::VALUE_REQUIRED,
                'ID of the decision to evaluate.'
            )
            ->addOption(
                '--contact-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'Evaluate for specific contact'
            )
            ->addOption(
                '--contact-ids',
                null,
                InputOption::VALUE_OPTIONAL,
                'CSV of contact IDs to evaluate.'
            );

        parent::configure();
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        defined('MAILVOTECH_CAMPAIGN_SYSTEM_TRIGGERED') || define('MAILVOTECH_CAMPAIGN_SYSTEM_TRIGGERED', 1);

        $decisionId = $input->getOption('decision-id');
        $contactId  = $input->getOption('contact-id');

        if (is_numeric($decisionId)) {
            $decisionId = (int) $decisionId;
        }

        if (is_numeric($contactId)) {
            $contactId = (int) $contactId;
        }

        $contactIds = $this->formatterHelper->simpleCsvToArray($input->getOption('contact-ids'), 'int');

        if (!$contactIds && !$contactId) {
            $output->writeln(
                "\n".
                '<comment>'.$this->translator->trans('mailvotech.campaign.trigger.events_executed', ['%count%' => 0])
                .'</comment>'
            );

            return Command::SUCCESS;
        }

        $limiter = new ContactLimiter(null, $contactId, null, null, $contactIds);
        $counter = $this->inactiveExecution->validate($decisionId, $limiter, $output);

        $this->writeCounts($output, $this->translator, $counter);

        return Command::SUCCESS;
    }
}
