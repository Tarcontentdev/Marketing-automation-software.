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

    $services->load('MailVotechPlugin\\MailVotechSocialBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotechPlugin\\MailVotechSocialBundle\\Entity\\', '../Entity/*Repository.php');
    $services->set('mailvotech.social.helper.campaign', MailVotechPlugin\MailVotechSocialBundle\Helper\CampaignEventHelper::class);
    $services->set('mailvotech.social.helper.twitter_command', MailVotechPlugin\MailVotechSocialBundle\Helper\TwitterCommandHelper::class);
    $services->set('mailvotech.integration.facebook', MailVotechPlugin\MailVotechSocialBundle\Integration\FacebookIntegration::class);
    $services->set('mailvotech.integration.foursquare', MailVotechPlugin\MailVotechSocialBundle\Integration\FoursquareIntegration::class);
    $services->set('mailvotech.integration.instagram', MailVotechPlugin\MailVotechSocialBundle\Integration\InstagramIntegration::class);
    $services->set('mailvotech.integration.twitter', MailVotechPlugin\MailVotechSocialBundle\Integration\TwitterIntegration::class);

    $services->alias('mailvotech.social.repository.lead', MailVotechPlugin\MailVotechSocialBundle\Entity\LeadRepository::class);
    $services->alias('mailvotech.social.model.monitoring', MailVotechPlugin\MailVotechSocialBundle\Model\MonitoringModel::class);
    $services->alias('mailvotech.social.model.postcount', MailVotechPlugin\MailVotechSocialBundle\Model\PostCountModel::class);
    $services->alias('mailvotech.social.model.tweet', MailVotechPlugin\MailVotechSocialBundle\Model\TweetModel::class);
};
