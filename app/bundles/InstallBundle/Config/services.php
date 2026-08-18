<?php

declare(strict_types=1);

use MailVotech\CoreBundle\DependencyInjection\MailVotechCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = [
        'Helper/SchemaHelper.php',
    ];

    $services->alias(MailVotech\CoreBundle\Doctrine\Loader\FixturesLoaderInterface::class, MailVotech\CoreBundle\Doctrine\Loader\MailVotechFixturesLoader::class);

    $services->load('MailVotech\\InstallBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');
    $services->set('mailvotech.install.fixture.lead_field', MailVotech\InstallBundle\InstallFixtures\ORM\LeadFieldData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\InstallBundle\InstallFixtures\ORM\LeadFieldData::class, 'mailvotech.install.fixture.lead_field');
    $services->set('mailvotech.install.fixture.role', MailVotech\InstallBundle\InstallFixtures\ORM\RoleData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\InstallBundle\InstallFixtures\ORM\RoleData::class, 'mailvotech.install.fixture.role');
    $services->set('mailvotech.install.fixture.report_data', MailVotech\InstallBundle\InstallFixtures\ORM\LoadReportData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\InstallBundle\InstallFixtures\ORM\LoadReportData::class, 'mailvotech.install.fixture.report_data');
    $services->set('mailvotech.install.configurator.step.check', MailVotech\InstallBundle\Configurator\Step\CheckStep::class)
        ->arg('$projectDir', param('kernel.project_dir'))
        ->tag('mailvotech.configurator.step', ['priority' => 0]);
    $services->alias(MailVotech\InstallBundle\Configurator\Step\CheckStep::class, 'mailvotech.install.configurator.step.check');
    $services->set('mailvotech.install.configurator.step.doctrine', MailVotech\InstallBundle\Configurator\Step\DoctrineStep::class)->tag('mailvotech.configurator.step', ['priority' => 1]);
    $services->set('mailvotech.install.configurator.step.user', MailVotech\InstallBundle\Configurator\Step\UserStep::class)->tag('mailvotech.configurator.step', ['priority' => 2]);
    $services->set('mailvotech.install.service', MailVotech\InstallBundle\Install\InstallService::class);
};
