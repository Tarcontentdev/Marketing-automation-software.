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

    $services->load('MailVotech\\PointBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\PointBundle\\Entity\\', '../Entity/*Repository.php')
        ->tag(Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\ServiceRepositoryCompilerPass::REPOSITORY_SERVICE_TAG);

    $services->alias('mailvotech.point.model.point', MailVotech\PointBundle\Model\PointModel::class);
    $services->alias('mailvotech.point.model.triggerevent', MailVotech\PointBundle\Model\TriggerEventModel::class);
    $services->alias('mailvotech.point.model.trigger', MailVotech\PointBundle\Model\TriggerModel::class);
    $services->alias('mailvotech.point.model.group', MailVotech\PointBundle\Model\PointGroupModel::class);
    $services->alias('mailvotech.point.repository.lead_point_log', MailVotech\PointBundle\Entity\LeadPointLogRepository::class);
    $services->alias('mailvotech.point.repository.lead_trigger_log', MailVotech\PointBundle\Entity\LeadTriggerLogRepository::class);
    $services->alias('mailvotech.point.model.insight', MailVotech\PointBundle\Model\InsightModel::class);
};
