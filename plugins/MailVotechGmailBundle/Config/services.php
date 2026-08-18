<?php

declare(strict_types=1);

use MailVotech\CoreBundle\DependencyInjection\MailVotechCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = [
    ];

    $services->load('MailVotechPlugin\\MailVotechGmailBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->alias('mailvotech.integration.gmail', MailVotechPlugin\MailVotechGmailBundle\Integration\GmailIntegration::class);
    $services->alias('mailvotech.integration.gmail.config', MailVotechPlugin\MailVotechGmailBundle\Integration\Support\ConfigSupport::class);
};
