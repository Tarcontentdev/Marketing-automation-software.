<?php

declare(strict_types=1);

use MailVotech\CoreBundle\DependencyInjection\MailVotechCoreExtension;
use MailVotech\PluginBundle\EventListener\CampaignSubscriber;
use MailVotech\PluginBundle\EventListener\FormSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = [
        'Helper/oAuthHelper.php',
        'Integration/IntegrationObject.php',
        'Form/Constraint/CanPublish.php',
    ];

    $services->load('MailVotech\\PluginBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\PluginBundle\\Entity\\', '../Entity/*Repository.php');
    $services->set('mailvotech.helper.integration', MailVotech\PluginBundle\Helper\IntegrationHelper::class);
    $services->alias(MailVotech\PluginBundle\Helper\IntegrationHelper::class, 'mailvotech.helper.integration');
    $services->set('mailvotech.plugin.helper.reload', MailVotech\PluginBundle\Helper\ReloadHelper::class);
    $services->alias(MailVotech\PluginBundle\Helper\ReloadHelper::class, 'mailvotech.plugin.helper.reload');
    $services->set('mailvotech.plugin.facade.reload', MailVotech\PluginBundle\Facade\ReloadFacade::class);
    $services->alias(MailVotech\PluginBundle\Facade\ReloadFacade::class, 'mailvotech.plugin.facade.reload');

    $services->alias('mailvotech.plugin.repository.integration', MailVotech\PluginBundle\Entity\IntegrationRepository::class);
    $services->alias('mailvotech.plugin.model.plugin', MailVotech\PluginBundle\Model\PluginModel::class);
    $services->alias('mailvotech.plugin.model.integration_entity', MailVotech\PluginBundle\Model\IntegrationEntityModel::class);

    $services->set(FormSubscriber::class)
        ->call('setIntegrationHelper', [service('mailvotech.helper.integration')]);
    $services->set(CampaignSubscriber::class)
        ->call('setIntegrationHelper', [service('mailvotech.helper.integration')]);
};
