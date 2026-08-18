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

    $services->load('MailVotechPlugin\\MailVotechCloudStorageBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');
    $services->set('mailvotech.integration.amazons3', MailVotechPlugin\MailVotechCloudStorageBundle\Integration\AmazonS3Integration::class);
    $services->alias(MailVotechPlugin\MailVotechCloudStorageBundle\Integration\AmazonS3Integration::class, 'mailvotech.integration.amazons3');
};
