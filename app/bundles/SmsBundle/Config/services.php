<?php

declare(strict_types=1);

use MailVotech\CoreBundle\DependencyInjection\MailVotechCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = ['Helper/DTO', 'Collection'];

    $services->load('MailVotech\\SmsBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\SmsBundle\\Entity\\', '../Entity/*Repository.php')
        ->tag(Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\ServiceRepositoryCompilerPass::REPOSITORY_SERVICE_TAG);

    $services->set('mailvotech.sms.twilio.transport', MailVotech\SmsBundle\Integration\Twilio\TwilioTransport::class)
        ->arg('$logger', service('monolog.logger.mailvotech'))
        ->tag('mailvotech.sms_transport', ['integrationAlias' => 'Twilio']);

    $services->alias(MailVotech\SmsBundle\Integration\Twilio\TwilioTransport::class, 'mailvotech.sms.twilio.transport');
    $services->alias('sms_api', 'mailvotech.sms.twilio.transport');
    $services->alias('mailvotech.sms.api', 'mailvotech.sms.twilio.transport');
    $services->set('mailvotech.helper.sms', MailVotech\SmsBundle\Helper\SmsHelper::class)->tag('twig.helper', ['alias' => 'sms_helper']);
    $services->alias(MailVotech\SmsBundle\Helper\SmsHelper::class, 'mailvotech.helper.sms');
    $services->set('mailvotech.sms.transport_chain', MailVotech\SmsBundle\Sms\TransportChain::class)
        ->arg('$primaryTransport', param('mailvotech.sms_transport'));
    $services->alias(MailVotech\SmsBundle\Sms\TransportChain::class, 'mailvotech.sms.transport_chain');
    $services->set('mailvotech.sms.helper.contact', MailVotech\SmsBundle\Helper\ContactHelper::class);
    $services->set('mailvotech.sms.helper.reply', MailVotech\SmsBundle\Helper\ReplyHelper::class);
    $services->set('mailvotech.sms.twilio.configuration', MailVotech\SmsBundle\Integration\Twilio\Configuration::class);
    $services->set('mailvotech.sms.twilio.callback', MailVotech\SmsBundle\Integration\Twilio\TwilioCallback::class)->tag('mailvotech.sms_callback_handler');
    $services->set('mailvotech.sms.broadcast.executioner', MailVotech\SmsBundle\Broadcast\BroadcastExecutioner::class);
    $services->set('mailvotech.sms.broadcast.query', MailVotech\SmsBundle\Broadcast\BroadcastQuery::class);
    $services->set('mailvotech.integration.twilio', MailVotech\SmsBundle\Integration\TwilioIntegration::class);

    $services->alias('mailvotech.sms.model.sms', MailVotech\SmsBundle\Model\SmsModel::class);
    $services->alias('mailvotech.sms.repository.stat', MailVotech\SmsBundle\Entity\StatRepository::class);
    $services->alias('mailvotech.sms.callback_handler_container', MailVotech\SmsBundle\Callback\HandlerContainer::class);
};
