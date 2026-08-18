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
        'PreferenceBuilder/ChannelPreferences.php',
        'PreferenceBuilder/PreferenceBuilder.php',
    ];

    $services->load('MailVotech\\ChannelBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\ChannelBundle\\Entity\\', '../Entity/*Repository.php')
        ->tag(Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\ServiceRepositoryCompilerPass::REPOSITORY_SERVICE_TAG);
    $services->alias('mailvotech.channel.model.message', MailVotech\ChannelBundle\Model\MessageModel::class);
    $services->alias('mailvotech.channel.model.queue', MailVotech\ChannelBundle\Model\MessageQueueModel::class);
    $services->alias('mailvotech.channel.model.channel.action', MailVotech\ChannelBundle\Model\ChannelActionModel::class);
    $services->alias('mailvotech.channel.model.frequency.action', MailVotech\ChannelBundle\Model\FrequencyActionModel::class);
    $services->alias('mailvotech.channel.repository.message_queue', MailVotech\ChannelBundle\Entity\MessageQueueRepository::class);

    $services->set(MailVotech\ChannelBundle\Helper\ChannelListHelper::class)
        ->tag('twig.helper', ['alias' => 'channel']);
};
