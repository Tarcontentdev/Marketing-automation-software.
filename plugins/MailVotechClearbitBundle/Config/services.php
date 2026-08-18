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
        'Services',
    ];

    $services->load('MailVotechPlugin\\MailVotechClearbitBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->alias('mailvotech.integration.clearbit', MailVotechPlugin\MailVotechClearbitBundle\Integration\ClearbitIntegration::class);
    $services->alias('mailvotech.integration.clearbit.config', MailVotechPlugin\MailVotechClearbitBundle\Integration\Support\ConfigSupport::class);
};
