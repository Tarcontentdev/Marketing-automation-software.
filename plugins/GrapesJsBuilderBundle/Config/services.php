<?php

declare(strict_types=1);

use MailVotech\CoreBundle\DependencyInjection\MailVotechCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('string $projectDir', '%kernel.project_dir%')
        ->public();

    $excludes = [
        'node_modules',
        'vendor',
    ];

    $services->load('MailVotechPlugin\\GrapesJsBuilderBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotechPlugin\\GrapesJsBuilderBundle\\Entity\\', '../Entity/*Repository.php');
    $services->set('grapesjsbuilder.config', MailVotechPlugin\GrapesJsBuilderBundle\Integration\Config::class);
    $services->alias(MailVotechPlugin\GrapesJsBuilderBundle\Integration\Config::class, 'grapesjsbuilder.config');
    $services->set('grapesjsbuilder.helper.filemanager', MailVotechPlugin\GrapesJsBuilderBundle\Helper\FileManager::class);
    $services->alias(MailVotechPlugin\GrapesJsBuilderBundle\Helper\FileManager::class, 'grapesjsbuilder.helper.filemanager');

    $services->alias('grapesjsbuilder.model', MailVotechPlugin\GrapesJsBuilderBundle\Model\GrapesJsBuilderModel::class);
    // Basic definitions with name, display name and icon
    $services->alias('mailvotech.integration.grapesjsbuilder', MailVotechPlugin\GrapesJsBuilderBundle\Integration\GrapesJsBuilderIntegration::class);
    // Provides the form types to use for the configuration UI
    $services->alias('grapesjsbuilder.integration.configuration', MailVotechPlugin\GrapesJsBuilderBundle\Integration\Support\ConfigSupport::class);
    // Tells MailVotech what themes it should support when enabled
    $services->alias('grapesjsbuilder.integration.builder', MailVotechPlugin\GrapesJsBuilderBundle\Integration\Support\BuilderSupport::class);

    $services->get(MailVotechPlugin\GrapesJsBuilderBundle\InstallFixtures\ORM\GrapesJsData::class)
        ->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
};
