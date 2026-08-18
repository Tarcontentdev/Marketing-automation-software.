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
        'Form/DataTransformer/EventsToArrayTransformer.php',
    ];

    $services->load('MailVotech\\WebhookBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\WebhookBundle\\Entity\\', '../Entity/*Repository.php')
        ->tag(Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\ServiceRepositoryCompilerPass::REPOSITORY_SERVICE_TAG);
    $services->set('mailvotech.webhook.campaign.helper', MailVotech\WebhookBundle\Helper\CampaignHelper::class)
        ->arg('$client', service('mailvotech.http.client'));
    $services->alias(MailVotech\WebhookBundle\Helper\CampaignHelper::class, 'mailvotech.webhook.campaign.helper');

    $services->alias('mailvotech.webhook.model.webhook', MailVotech\WebhookBundle\Model\WebhookModel::class);
    $services->alias('mailvotech.webhook.repository.queue', MailVotech\WebhookBundle\Entity\WebhookQueueRepository::class);
};
