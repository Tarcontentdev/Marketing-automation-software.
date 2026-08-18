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
        'Api',
        'Integration/Salesforce',
    ];

    $services->load('MailVotechPlugin\\MailVotechCrmBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');
    $services->set('mailvotech_integration.service.transport', MailVotechPlugin\MailVotechCrmBundle\Services\Transport::class)
        ->arg('$client', service('mailvotech.http.client'));
    $services->alias(MailVotechPlugin\MailVotechCrmBundle\Services\Transport::class, 'mailvotech_integration.service.transport');

    $services->alias('mailvotech.integration.hubspot', MailVotechPlugin\MailVotechCrmBundle\Integration\HubspotIntegration::class);
    $services->alias('mailvotech.integration.salesforce', MailVotechPlugin\MailVotechCrmBundle\Integration\SalesforceIntegration::class);
    $services->alias('mailvotech.integration.sugarcrm', MailVotechPlugin\MailVotechCrmBundle\Integration\SugarcrmIntegration::class);
    $services->alias('mailvotech.integration.vtiger', MailVotechPlugin\MailVotechCrmBundle\Integration\VtigerIntegration::class);
    $services->alias('mailvotech.integration.zoho', MailVotechPlugin\MailVotechCrmBundle\Integration\ZohoIntegration::class);
    $services->alias('mailvotech.integration.dynamics', MailVotechPlugin\MailVotechCrmBundle\Integration\DynamicsIntegration::class);
    $services->alias('mailvotech.integration.connectwise', MailVotechPlugin\MailVotechCrmBundle\Integration\ConnectwiseIntegration::class);
};
