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
        'OptionsAccessor',
        'MonitoredEmail/Accessor',
        'MonitoredEmail/Organizer',
        'MonitoredEmail/Processor',
        'Stat/Reference.php',
        'Helper/DTO',
        'Model/AbTest/EmailStatus.php',
    ];

    $services->load('MailVotech\\EmailBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\EmailBundle\\Entity\\', '../Entity/*Repository.php')
        ->tag(Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\ServiceRepositoryCompilerPass::REPOSITORY_SERVICE_TAG);
    $services->set('mailvotech.email.fixture.email', MailVotech\EmailBundle\DataFixtures\ORM\LoadEmailData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\EmailBundle\DataFixtures\ORM\LoadEmailData::class, 'mailvotech.email.fixture.email');
    $services->set('mailvotech.di.env_processor.mailerdsn', MailVotech\EmailBundle\DependencyInjection\EnvProcessor\MailerDsnEnvVarProcessor::class)->tag('container.env_var_processor');
    $services->set('mailvotech.message.search.contact', MailVotech\EmailBundle\MonitoredEmail\Search\ContactFinder::class);

    $services->set(MailVotech\EmailBundle\MonitoredEmail\Processor\Unsubscribe::class);
    $services->alias('mailvotech.message.processor.unsubscribe', MailVotech\EmailBundle\MonitoredEmail\Processor\Unsubscribe::class);

    $services->set(MailVotech\EmailBundle\MonitoredEmail\Processor\FeedbackLoop::class);
    $services->alias('mailvotech.message.processor.feedbackloop', MailVotech\EmailBundle\MonitoredEmail\Processor\FeedbackLoop::class);

    $services->set('mailvotech.validator.email', MailVotech\EmailBundle\Helper\EmailValidator::class);
    $services->set('mailvotech.email.fetcher', MailVotech\EmailBundle\MonitoredEmail\Fetcher::class);
    $services->set('mailvotech.email.helper.stats_collection', MailVotech\EmailBundle\Helper\StatsCollectionHelper::class);
    $services->set('mailvotech.email.stats.helper_container', MailVotech\EmailBundle\Stats\StatHelperContainer::class);

    $services->set('mailvotech.email.stats.helper_bounced', MailVotech\EmailBundle\Stats\Helper\BouncedHelper::class)
        ->tag('mailvotech.email_stat_helper');
    $services->set('mailvotech.email.stats.helper_clicked', MailVotech\EmailBundle\Stats\Helper\ClickedHelper::class)
        ->tag('mailvotech.email_stat_helper');
    $services->set('mailvotech.email.stats.helper_failed', MailVotech\EmailBundle\Stats\Helper\FailedHelper::class)
        ->tag('mailvotech.email_stat_helper');
    $services->set('mailvotech.email.stats.helper_opened', MailVotech\EmailBundle\Stats\Helper\OpenedHelper::class)
        ->tag('mailvotech.email_stat_helper');
    $services->set('mailvotech.email.stats.helper_sent', MailVotech\EmailBundle\Stats\Helper\SentHelper::class)
        ->tag('mailvotech.email_stat_helper');
    $services->set('mailvotech.email.stats.helper_unsubscribed', MailVotech\EmailBundle\Stats\Helper\UnsubscribedHelper::class)
        ->tag('mailvotech.email_stat_helper');

    $services->set('mailvotech.email.validator.multiple_emails_valid_validator', MailVotech\EmailBundle\Validator\MultipleEmailsValidValidator::class)->tag('validator.constraint_validator');
    $services->set('mailvotech.email.validator.email_or_token_list_validator', MailVotech\EmailBundle\Validator\EmailOrEmailTokenListValidator::class)->tag('validator.constraint_validator');

    $services->alias(MailVotech\CoreBundle\Doctrine\Provider\GeneratedColumnsProviderInterface::class, MailVotech\CoreBundle\Doctrine\Provider\GeneratedColumnsProvider::class);
    $services->set(MailVotech\EmailBundle\Mailer\Transport\TransportFactory::class)->decorate('mailer.transport_factory');

    $services->set(MailVotech\EmailBundle\MonitoredEmail\Processor\Bounce::class);
    $services->set(MailVotech\EmailBundle\MonitoredEmail\Processor\Reply::class);

    $services->alias('mailvotech.email.model.email', MailVotech\EmailBundle\Model\EmailModel::class);
    $services->alias('mailvotech.email.model.send_email_to_user', MailVotech\EmailBundle\Model\SendEmailToUser::class);
    $services->alias('mailvotech.email.model.send_email_to_contacts', MailVotech\EmailBundle\Model\SendEmailToContact::class);
    $services->alias('mailvotech.email.model.transport_callback', MailVotech\EmailBundle\Model\TransportCallback::class);
    $services->alias('mailvotech.email.repository.email', MailVotech\EmailBundle\Entity\EmailRepository::class);
    $services->alias('mailvotech.email.repository.emailReply', MailVotech\EmailBundle\Entity\EmailReplyRepository::class);
    $services->alias('mailvotech.email.repository.stat', MailVotech\EmailBundle\Entity\StatRepository::class);
    $services->alias('mailvotech.helper.mailbox', MailVotech\EmailBundle\MonitoredEmail\Mailbox::class);
    $services->alias('mailvotech.helper.mailer', MailVotech\EmailBundle\Helper\MailHelper::class);
    $services->alias('mailvotech.message.processor.bounce', MailVotech\EmailBundle\MonitoredEmail\Processor\Bounce::class);
    $services->alias('mailvotech.message.processor.replier', MailVotech\EmailBundle\MonitoredEmail\Processor\Reply::class);
    $services->alias('mailvotech.email.helper.stat', MailVotech\EmailBundle\Stat\StatHelper::class);
    $services->alias('mailvotech.email.stats.helper_container', MailVotech\EmailBundle\Stats\StatHelperContainer::class);

    $services->get(MailVotech\EmailBundle\EventListener\WebhookSubscriber::class)
        ->arg('$includeDetails', '%mailvotech.webhook_email_details%');
};
