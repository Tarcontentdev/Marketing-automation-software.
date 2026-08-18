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

    $services->set(MailVotech\DynamicContentBundle\Form\Type\DwcEntryFiltersType::class)
        ->call('setConnection', [service('database_connection')]);

    $services->load('MailVotech\\DynamicContentBundle\\', '../')
        ->exclude('../{'.implode(',', MailVotechCoreExtension::DEFAULT_EXCLUDES).'}');

    $services->load('MailVotech\\DynamicContentBundle\\Entity\\', '../Entity/*Repository.php')
        ->tag(Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\ServiceRepositoryCompilerPass::REPOSITORY_SERVICE_TAG);
    $services->set('mailvotech.helper.dynamicContent', MailVotech\DynamicContentBundle\Helper\DynamicContentHelper::class);
    $services->alias(MailVotech\DynamicContentBundle\Helper\DynamicContentHelper::class, 'mailvotech.helper.dynamicContent');
    $services->alias('mailvotech.dynamicContent.model.dynamicContent', MailVotech\DynamicContentBundle\Model\DynamicContentModel::class);
    $services->alias('mailvotech.dynamicContent.repository.stat', MailVotech\DynamicContentBundle\Entity\StatRepository::class);
};
