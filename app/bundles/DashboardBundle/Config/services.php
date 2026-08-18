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

    $services->load('MailVotech\\DashboardBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\DashboardBundle\\Entity\\', '../Entity/*Repository.php');
    $services->set('mailvotech.dashboard.widget', MailVotech\DashboardBundle\Dashboard\Widget::class);
    $services->alias(MailVotech\DashboardBundle\Dashboard\Widget::class, 'mailvotech.dashboard.widget');
    $services->alias('mailvotech.dashboard.model.dashboard', MailVotech\DashboardBundle\Model\DashboardModel::class);
};
