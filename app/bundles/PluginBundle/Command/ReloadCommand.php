<?php

declare(strict_types=1);

namespace MailVotech\PluginBundle\Command;

use MailVotech\PluginBundle\Facade\ReloadFacade;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'mailvotech:plugins:reload',
    description: 'Installs, updates, enable and/or disable plugins.',
    aliases: [
        'mailvotech:plugins:install',
        'mailvotech:plugins:update',
    ]
)]
final class ReloadCommand extends Command
{
    public function __construct(
        private readonly ReloadFacade $reloadFacade,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeLn($this->reloadFacade->reloadPlugins());

        return Command::SUCCESS;
    }
}
