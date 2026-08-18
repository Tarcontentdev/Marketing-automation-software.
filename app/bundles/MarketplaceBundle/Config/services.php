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

    $excludes = [];

    $services->load('MailVotech\\MarketplaceBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->set('marketplace.permissions', MailVotech\MarketplaceBundle\Security\Permissions\MarketplacePermissions::class)->tag('mailvotech.permissions');

    $services->alias(MailVotech\MarketplaceBundle\Security\Permissions\MarketplacePermissions::class, 'marketplace.permissions');

    $services->set(MailVotech\MarketplaceBundle\Api\Connection::class)
        ->arg('$httpClient', \Symfony\Component\DependencyInjection\Loader\Configurator\service('mailvotech.http.client'));

    $services->set('marketplace.service.plugin_collector', MailVotech\MarketplaceBundle\Service\PluginCollector::class);
    $services->alias(MailVotech\MarketplaceBundle\Service\PluginCollector::class, 'marketplace.service.plugin_collector');
    $services->set('marketplace.service.route_provider', MailVotech\MarketplaceBundle\Service\RouteProvider::class);
    $services->alias(MailVotech\MarketplaceBundle\Service\RouteProvider::class, 'marketplace.service.route_provider');
    $services->set('marketplace.service.config', MailVotech\MarketplaceBundle\Service\Config::class);
    $services->alias(MailVotech\MarketplaceBundle\Service\Config::class, 'marketplace.service.config');

    $services->set(MailVotech\MarketplaceBundle\Service\Allowlist::class)
        ->arg('$httpClient', \Symfony\Component\DependencyInjection\Loader\Configurator\service('mailvotech.http.client'));

    $services->alias('marketplace.model.package', MailVotech\MarketplaceBundle\Model\PackageModel::class);
};
