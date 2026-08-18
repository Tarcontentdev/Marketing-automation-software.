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

    $services->load('MailVotechPlugin\\MailVotechOutlookBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');
    $services->set('mailvotech.integration.outlook', MailVotechPlugin\MailVotechOutlookBundle\Integration\OutlookIntegration::class);
    $services->alias(MailVotechPlugin\MailVotechOutlookBundle\Integration\OutlookIntegration::class, 'mailvotech.integration.outlook');
};
