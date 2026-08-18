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
        'EventCollector/Accessor',
        'Executioner/ContactFinder/Limiter/ContactLimiter.php',
        'Executioner/Dispatcher/Exception',
        'Executioner/Scheduler/Mode/DAO',
        'Membership/Exception',
    ];

    $services->load('MailVotech\\CampaignBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\CampaignBundle\\Entity\\', '../Entity/*Repository.php')
        ->tag(Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\ServiceRepositoryCompilerPass::REPOSITORY_SERVICE_TAG);

    $services->set(MailVotech\CampaignBundle\DataFixtures\ORM\CampaignData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);

    $services->set('mailvotech.campaign.contact_finder.kickoff', MailVotech\CampaignBundle\Executioner\ContactFinder\KickoffContactFinder::class);
    $services->set('mailvotech.campaign.contact_finder.scheduled', MailVotech\CampaignBundle\Executioner\ContactFinder\ScheduledContactFinder::class);
    $services->set('mailvotech.campaign.contact_finder.inactive', MailVotech\CampaignBundle\Executioner\ContactFinder\InactiveContactFinder::class);
    $services->set('mailvotech.campaign.dispatcher.action', MailVotech\CampaignBundle\Executioner\Dispatcher\ActionDispatcher::class);
    $services->set('mailvotech.campaign.dispatcher.condition', MailVotech\CampaignBundle\Executioner\Dispatcher\ConditionDispatcher::class);
    $services->set('mailvotech.campaign.dispatcher.decision', MailVotech\CampaignBundle\Executioner\Dispatcher\DecisionDispatcher::class);
    $services->set('mailvotech.campaign.scheduler.datetime', MailVotech\CampaignBundle\Executioner\Scheduler\Mode\DateTime::class);
    $services->set('mailvotech.campaign.scheduler.interval', MailVotech\CampaignBundle\Executioner\Scheduler\Mode\Interval::class);
    $services->set('mailvotech.campaign.executioner.condition', MailVotech\CampaignBundle\Executioner\Event\ConditionExecutioner::class);
    $services->set('mailvotech.campaign.executioner.decision', MailVotech\CampaignBundle\Executioner\Event\DecisionExecutioner::class);
    $services->set('mailvotech.campaign.event_executioner', MailVotech\CampaignBundle\Executioner\EventExecutioner::class);
    $services->set('mailvotech.campaign.helper.decision', MailVotech\CampaignBundle\Executioner\Helper\DecisionHelper::class);
    $services->set('mailvotech.campaign.helper.inactivity', MailVotech\CampaignBundle\Executioner\Helper\InactiveHelper::class);
    $services->set('mailvotech.campaign.helper.removed_contact_tracker', MailVotech\CampaignBundle\Helper\RemovedContactTracker::class);
    $services->set('mailvotech.campaign.helper.notification', MailVotech\CampaignBundle\Executioner\Helper\NotificationHelper::class);
    $services->set('mailvotech.campaign.legacy_event_dispatcher', MailVotech\CampaignBundle\Executioner\Dispatcher\LegacyEventDispatcher::class);
    $services->set('mailvotech.campaign.membership.adder', MailVotech\CampaignBundle\Membership\Action\Adder::class);
    $services->set('mailvotech.campaign.membership.remover', MailVotech\CampaignBundle\Membership\Action\Remover::class);
    $services->set('mailvotech.campaign.membership.event_dispatcher', MailVotech\CampaignBundle\Membership\EventDispatcher::class);
    $services->set('mailvotech.campaign.membership.manager', MailVotech\CampaignBundle\Membership\MembershipManager::class);
    $services->set('mailvotech.campaign.membership.builder', MailVotech\CampaignBundle\Membership\MembershipBuilder::class);
    $services->alias('mailvotech.campaign.model.campaign', MailVotech\CampaignBundle\Model\CampaignModel::class);
    $services->alias('mailvotech.campaign.model.event', MailVotech\CampaignBundle\Model\EventModel::class);
    $services->alias('mailvotech.campaign.model.event_log', MailVotech\CampaignBundle\Model\EventLogModel::class);
    $services->alias('mailvotech.campaign.model.summary', MailVotech\CampaignBundle\Model\SummaryModel::class);
    $services->alias('mailvotech.campaign.repository.campaign', MailVotech\CampaignBundle\Entity\CampaignRepository::class);
    $services->alias('mailvotech.campaign.repository.lead', MailVotech\CampaignBundle\Entity\LeadRepository::class);
    $services->alias('mailvotech.campaign.repository.event', MailVotech\CampaignBundle\Entity\EventRepository::class);
    $services->alias('mailvotech.campaign.repository.lead_event_log', MailVotech\CampaignBundle\Entity\LeadEventLogRepository::class);
    $services->alias('mailvotech.campaign.repository.summary', MailVotech\CampaignBundle\Entity\SummaryRepository::class);
    $services->alias('mailvotech.campaign.executioner.inactive', MailVotech\CampaignBundle\Executioner\InactiveExecutioner::class);
    $services->alias('mailvotech.campaign.executioner.scheduled', MailVotech\CampaignBundle\Executioner\ScheduledExecutioner::class);
    $services->alias('mailvotech.campaign.scheduler.optimized', MailVotech\CampaignBundle\Executioner\Scheduler\Mode\Optimized::class);
    $services->alias('mailvotech.campaign.event_logger', MailVotech\CampaignBundle\Executioner\Logger\EventLogger::class);
    $services->alias('mailvotech.campaign.executioner.kickoff', MailVotech\CampaignBundle\Executioner\KickoffExecutioner::class);
    $services->alias('mailvotech.campaign.scheduler', MailVotech\CampaignBundle\Executioner\Scheduler\EventScheduler::class);
    $services->alias('mailvotech.campaign.executioner.action', MailVotech\CampaignBundle\Executioner\Event\ActionExecutioner::class);
    $services->alias('mailvotech.campaign.executioner.realtime', MailVotech\CampaignBundle\Executioner\RealTimeExecutioner::class);
    $services->set(MailVotech\CampaignBundle\Executioner\ScheduledExecutioner::class)->tag('kernel.reset', ['method' => 'reset']);

    if ('test' === ($_ENV['APP_ENV'] ?? $_SERVER['APP_ENV'] ?? 'prod')) {
        $services->set(MailVotech\CampaignBundle\Executioner\TestInactiveExecutioner::class)
            ->decorate(MailVotech\CampaignBundle\Executioner\InactiveExecutioner::class)
            ->tag('kernel.reset', ['method' => 'reset']);

        $services->set(MailVotech\CampaignBundle\Executioner\TestScheduledExecutioner::class)
            ->decorate(MailVotech\CampaignBundle\Executioner\ScheduledExecutioner::class)
            ->tag('kernel.reset', ['method' => 'reset']);
    }
};
