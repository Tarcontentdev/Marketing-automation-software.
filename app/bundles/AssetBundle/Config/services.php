<?php

declare(strict_types=1);

use MailVotech\CoreBundle\DependencyInjection\MailVotechCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $configurator): void {
    $parameters = $configurator->parameters();
    $parameters->set('oneup_uploader.controller.dropzone.class', MailVotech\AssetBundle\Controller\UploadController::class);

    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = [
        'Controller/UploadController.php',
    ];

    $services->load('MailVotech\\AssetBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\AssetBundle\\Entity\\', '../Entity/*Repository.php')
        ->tag(Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\ServiceRepositoryCompilerPass::REPOSITORY_SERVICE_TAG);

    $services->set('mailvotech.asset.fixture.asset', MailVotech\AssetBundle\DataFixtures\ORM\LoadAssetData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\AssetBundle\DataFixtures\ORM\LoadAssetData::class, 'mailvotech.asset.fixture.asset');
    $services->set('mailvotech.asset.permissions', MailVotech\AssetBundle\Security\Permissions\AssetPermissions::class)->tag('mailvotech.permissions');
    $services->alias(MailVotech\AssetBundle\Security\Permissions\AssetPermissions::class, 'mailvotech.asset.permissions');
    $services->set('mailvotech.asset.upload.error.handler', MailVotech\AssetBundle\ErrorHandler\DropzoneErrorHandler::class);
    $services->alias(MailVotech\AssetBundle\ErrorHandler\DropzoneErrorHandler::class, 'mailvotech.asset.upload.error.handler');
    $services->alias('mailvotech.asset.helper.token', MailVotech\AssetBundle\Helper\TokenHelper::class);
    $services->alias('mailvotech.asset.model.asset', MailVotech\AssetBundle\Model\AssetModel::class);
    $services->alias(Oneup\UploaderBundle\Templating\Helper\UploaderHelper::class, 'oneup_uploader.templating.uploader_helper');
    $services->alias('mailvotech.asset.repository.download', MailVotech\AssetBundle\Entity\DownloadRepository::class);
};
