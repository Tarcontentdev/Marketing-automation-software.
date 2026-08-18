<?php

declare(strict_types=1);

use MailVotech\CoreBundle\DependencyInjection\MailVotechCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = [
        'Services',
    ];

    $services->load('MailVotechPlugin\\MailVotechFullContactBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');
    $services->set('mailvotech.plugin.fullcontact.lookup_helper', MailVotechPlugin\MailVotechFullContactBundle\Helper\LookupHelper::class)
        ->arg('$logger', service('monolog.logger.mailvotech'))
        ->arg('$router', service('router'));
    $services->alias(MailVotechPlugin\MailVotechFullContactBundle\Helper\LookupHelper::class, 'mailvotech.plugin.fullcontact.lookup_helper');
    $services->set('mailvotech.integration.fullcontact', MailVotechPlugin\MailVotechFullContactBundle\Integration\FullContactIntegration::class);
    $services->alias(MailVotechPlugin\MailVotechFullContactBundle\Integration\FullContactIntegration::class, 'mailvotech.integration.fullcontact');
};
