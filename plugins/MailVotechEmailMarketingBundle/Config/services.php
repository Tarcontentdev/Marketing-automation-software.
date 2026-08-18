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
        'Api',
    ];

    $services->load('MailVotechPlugin\\MailVotechEmailMarketingBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');
    $services->set('mailvotech.integration.constantcontact', MailVotechPlugin\MailVotechEmailMarketingBundle\Integration\ConstantContactIntegration::class);
    $services->alias(MailVotechPlugin\MailVotechEmailMarketingBundle\Integration\ConstantContactIntegration::class, 'mailvotech.integration.constantcontact');
    $services->set('mailvotech.integration.icontact', MailVotechPlugin\MailVotechEmailMarketingBundle\Integration\IcontactIntegration::class);
    $services->alias(MailVotechPlugin\MailVotechEmailMarketingBundle\Integration\IcontactIntegration::class, 'mailvotech.integration.icontact');
    $services->set('mailvotech.integration.mailchimp', MailVotechPlugin\MailVotechEmailMarketingBundle\Integration\MailchimpIntegration::class);
    $services->alias(MailVotechPlugin\MailVotechEmailMarketingBundle\Integration\MailchimpIntegration::class, 'mailvotech.integration.mailchimp');
};
