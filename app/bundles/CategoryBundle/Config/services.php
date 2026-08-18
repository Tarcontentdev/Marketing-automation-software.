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
    ];

    $services->load('MailVotech\\CategoryBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\CategoryBundle\\Entity\\', '../Entity/*Repository.php');
    $services->alias('mailvotech.category.repository.category', MailVotech\CategoryBundle\Entity\CategoryRepository::class);
    $services->alias('mailvotech.category.model.category', MailVotech\CategoryBundle\Model\CategoryModel::class);
    $services->alias('mailvotech.category.model.contact.action', MailVotech\CategoryBundle\Model\ContactActionModel::class);
};
