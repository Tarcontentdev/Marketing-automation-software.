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

    $excludes = [];

    $services->load('MailVotech\\NotificationBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\NotificationBundle\\Entity\\', '../Entity/*Repository.php')
        ->tag(Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\ServiceRepositoryCompilerPass::REPOSITORY_SERVICE_TAG);
    $services->set('mailvotech.notification.campaignbundle.subscriber', MailVotech\NotificationBundle\EventListener\CampaignSubscriber::class)
        ->arg('$notificationApi', service('mailvotech.notification.api'));
    $services->alias(MailVotech\NotificationBundle\EventListener\CampaignSubscriber::class, 'mailvotech.notification.campaignbundle.subscriber');
    $services->set('mailvotech.integration.onesignal', MailVotech\NotificationBundle\Integration\OneSignalIntegration::class);
    $services->alias(MailVotech\NotificationBundle\Integration\OneSignalIntegration::class, 'mailvotech.integration.onesignal');

    $services->alias('mailvotech.notification.model.notification', MailVotech\NotificationBundle\Model\NotificationModel::class);
    $services->alias('mailvotech.notification.repository.stat', MailVotech\NotificationBundle\Entity\StatRepository::class);
    $services->alias('mailvotech.helper.notification', MailVotech\NotificationBundle\Helper\NotificationHelper::class);
    $services->alias('notification_helper', MailVotech\NotificationBundle\Helper\NotificationHelper::class);

    $services->alias('mailvotech.notification.api', MailVotech\NotificationBundle\Api\OneSignalApi::class);
};
