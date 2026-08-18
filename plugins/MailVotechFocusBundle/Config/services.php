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

    $services->load('MailVotechPlugin\\MailVotechFocusBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotechPlugin\\MailVotechFocusBundle\\Entity\\', '../Entity/*Repository.php')
        ->tag(Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\ServiceRepositoryCompilerPass::REPOSITORY_SERVICE_TAG);
    $services->set('mailvotech.focus.helper.token', MailVotechPlugin\MailVotechFocusBundle\Helper\TokenHelper::class);
    $services->alias(MailVotechPlugin\MailVotechFocusBundle\Helper\TokenHelper::class, 'mailvotech.focus.helper.token');

    $services->alias('mailvotech.focus.model.focus', MailVotechPlugin\MailVotechFocusBundle\Model\FocusModel::class);
};
