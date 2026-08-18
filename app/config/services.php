<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use MailVotech\CoreBundle\DependencyInjection\MailVotechCoreExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

// This is loaded by \MailVotech\CoreBundle\DependencyInjection\MailVotechCoreExtension to auto-wire services
// if the bundle do not cover it itself by their own *Extension and services.php which is prefered.
return function (ContainerConfigurator $configurator, ContainerBuilder $container) {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $bundles = array_merge($container->getParameter('mailvotech.bundles'), $container->getParameter('mailvotech.plugin.bundles'));

    // Autoconfigure services for bundles that do not have its own Config/services.php
    foreach ($bundles as $bundle) {
        if (file_exists($bundle['directory'].'/Config/services.php')) {
            continue;
        }

        $services->load($bundle['namespace'].'\\', $bundle['directory'])
            ->exclude($bundle['directory'].'/{'.implode(',', MailVotechCoreExtension::DEFAULT_EXCLUDES).'}');

        if (is_dir($bundle['directory'].'/Entity')) {
            $services->load($bundle['namespace'].'\\Entity\\', $bundle['directory'].'/Entity/*Repository.php');
        }
    }
};
