<?php

declare(strict_types=1);

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\ServiceRepositoryCompilerPass;
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

    $services->load('MailVotechPlugin\\MailVotechTagManagerBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotechPlugin\\MailVotechTagManagerBundle\\Entity\\', '../Entity/*Repository.php')
        ->tag(ServiceRepositoryCompilerPass::REPOSITORY_SERVICE_TAG);

    $services->alias('mailvotech.tagmanager.model.tag', MailVotechPlugin\MailVotechTagManagerBundle\Model\TagModel::class);
    $services->alias('mailvotech.tagmanager.repository.tag', MailVotechPlugin\MailVotechTagManagerBundle\Entity\TagRepository::class);
    $services->alias('mailvotech.integration.tagmanager', MailVotechPlugin\MailVotechTagManagerBundle\Integration\TagManagerIntegration::class);
    $services->alias('mailvotech.integration.tagmanager.config', MailVotechPlugin\MailVotechTagManagerBundle\Integration\Support\ConfigSupport::class);
};
