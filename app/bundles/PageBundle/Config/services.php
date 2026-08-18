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

    $services->load('MailVotech\\PageBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\PageBundle\\Entity\\', '../Entity/*Repository.php')
        ->tag(Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\ServiceRepositoryCompilerPass::REPOSITORY_SERVICE_TAG);
    $services->set('mailvotech.page.fixture.page_category', MailVotech\PageBundle\DataFixtures\ORM\LoadPageCategoryData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\PageBundle\DataFixtures\ORM\LoadPageCategoryData::class, 'mailvotech.page.fixture.page_category');
    $services->set('mailvotech.page.fixture.page', MailVotech\PageBundle\DataFixtures\ORM\LoadPageData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\PageBundle\DataFixtures\ORM\LoadPageData::class, 'mailvotech.page.fixture.page');
    $services->set('mailvotech.page.fixture.page_hit', MailVotech\PageBundle\DataFixtures\ORM\LoadPageHitData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\PageBundle\DataFixtures\ORM\LoadPageHitData::class, 'mailvotech.page.fixture.page_hit');
    $services->set('mailvotech.page.segment_tracking_subscriber', MailVotech\PageBundle\EventListener\SegmentTrackingSubscriber::class);
    $services->set('mailvotech.page.helper.token', MailVotech\PageBundle\Helper\TokenHelper::class);
    $services->set('mailvotech.page.helper.tracking', MailVotech\PageBundle\Helper\TrackingHelper::class);

    $services->get(MailVotech\PageBundle\Model\PageModel::class)->call('setCatInUrl', ['%mailvotech.cat_in_page_url%']);
    $services->alias('mailvotech.page.model.page', MailVotech\PageBundle\Model\PageModel::class);
    $services->alias('mailvotech.page.model.redirect', MailVotech\PageBundle\Model\RedirectModel::class);
    $services->alias('mailvotech.page.model.trackable', MailVotech\PageBundle\Model\TrackableModel::class);
    $services->alias('mailvotech.page.model.video', MailVotech\PageBundle\Model\VideoModel::class);
    $services->alias('mailvotech.page.model.tracking.404', MailVotech\PageBundle\Model\Tracking404Model::class);
    $services->alias('mailvotech.page.repository.hit', MailVotech\PageBundle\Entity\HitRepository::class);
    $services->alias('mailvotech.page.repository.page', MailVotech\PageBundle\Entity\PageRepository::class);
    $services->alias('mailvotech.page.repository.redirect', MailVotech\PageBundle\Entity\RedirectRepository::class);
};
