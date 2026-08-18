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
        'Aggregate/Collection',
        'Aggregate/Calculator.php',
    ];

    $services->load('MailVotech\\StatsBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');
    $services->set('mailvotech.stats.aggregate.collector', MailVotech\StatsBundle\Aggregate\Collector::class);
    $services->alias(MailVotech\StatsBundle\Aggregate\Collector::class, 'mailvotech.stats.aggregate.collector');
};
