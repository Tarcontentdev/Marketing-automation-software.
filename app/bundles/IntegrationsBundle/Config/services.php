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
        'Helper/FieldFilterHelper.php',
        'Helper/FieldMergerHelper.php',
        'Auth/Support/Oauth2/Token',
        'Sync/DAO',
        'Sync/Exception',
        'Sync/SyncDataExchange/Internal/Executioner/Exception',
        'Sync/SyncProcess/SyncProcess.php',
        'Integration/IntegrationObject.php',
    ];

    $services->set(MailVotech\IntegrationsBundle\Sync\SyncService\SyncService::class)
        ->call('initiateDebugLogger', [\Symfony\Component\DependencyInjection\Loader\Configurator\service('mailvotech.sync.logger')]);

    $services->load('MailVotech\\IntegrationsBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\IntegrationsBundle\\Entity\\', '../Entity/*Repository.php')
        ->tag(Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\ServiceRepositoryCompilerPass::REPOSITORY_SERVICE_TAG);

    $services->set(MailVotech\IntegrationsBundle\EventListener\ControllerSubscriber::class)
        ->arg('$resolver', \Symfony\Component\DependencyInjection\Loader\Configurator\service('controller_resolver'));

    $services->set('mailvotech.integrations.helper.variable_expresser', MailVotech\IntegrationsBundle\Sync\VariableExpresser\VariableExpresserHelper::class);
    $services->set('mailvotech.integrations.helper.field_validator', MailVotech\IntegrationsBundle\Helper\FieldValidationHelper::class);
    $services->set('mailvotech.integrations.service.encryption', MailVotech\IntegrationsBundle\Facade\EncryptionService::class);
    $services->set('mailvotech.integrations.internal.object_provider', MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectProvider::class);
    $services->set('mailvotech.integrations.sync.notification.helper.owner_provider', MailVotech\IntegrationsBundle\Sync\Notification\Helper\OwnerProvider::class);
    $services->set('mailvotech.integrations.auth_provider.api_key', MailVotech\IntegrationsBundle\Auth\Provider\ApiKey\HttpFactory::class);
    $services->set('mailvotech.integrations.auth_provider.basic_auth', MailVotech\IntegrationsBundle\Auth\Provider\BasicAuth\HttpFactory::class);
    $services->set('mailvotech.integrations.auth_provider.oauth1atwolegged', MailVotech\IntegrationsBundle\Auth\Provider\Oauth1aTwoLegged\HttpFactory::class);
    $services->set('mailvotech.integrations.auth_provider.oauth2twolegged', MailVotech\IntegrationsBundle\Auth\Provider\Oauth2TwoLegged\HttpFactory::class);
    $services->set('mailvotech.integrations.auth_provider.oauth2threelegged', MailVotech\IntegrationsBundle\Auth\Provider\Oauth2ThreeLegged\HttpFactory::class);
    $services->set('mailvotech.integrations.auth_provider.token_persistence_factory', MailVotech\IntegrationsBundle\Auth\Support\Oauth2\Token\TokenPersistenceFactory::class);
    $services->set('mailvotech.integrations.token.parser', MailVotech\IntegrationsBundle\Helper\TokenParser::class);
    $services->set('mailvotech.sync.logger', MailVotech\IntegrationsBundle\Sync\Logger\DebugLogger::class);
    $services->set('mailvotech.integrations.helper.sync_judge', MailVotech\IntegrationsBundle\Sync\SyncJudge\SyncJudge::class);
    $services->set('mailvotech.integrations.sync.data_exchange.mailvotech.order_executioner', MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Executioner\OrderExecutioner::class);
    $services->set('mailvotech.integrations.internal.field_validator', MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Executioner\FieldValidator::class);
    $services->set('mailvotech.integrations.internal.reference_resolver', MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Executioner\ReferenceResolver::class);
    $services->set('mailvotech.integrations.sync.sync_process.value_helper', MailVotech\IntegrationsBundle\Sync\SyncProcess\Direction\Helper\ValueHelper::class);
    $services->set('mailvotech.integrations.sync.data_exchange.mailvotech.field_builder', MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ReportBuilder\FieldBuilder::class);
    $services->set('mailvotech.integrations.sync.data_exchange.mailvotech.full_object_report_builder', MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ReportBuilder\FullObjectReportBuilder::class);
    $services->set('mailvotech.integrations.sync.data_exchange.mailvotech.partial_object_report_builder', MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ReportBuilder\PartialObjectReportBuilder::class);
    $services->set('mailvotech.integrations.sync.data_exchange.mailvotech', MailVotech\IntegrationsBundle\Sync\SyncDataExchange\MailVotechSyncDataExchange::class);
    $services->set('mailvotech.integrations.sync.integration_process.object_change_generator', MailVotech\IntegrationsBundle\Sync\SyncProcess\Direction\Integration\ObjectChangeGenerator::class);
    $services->set('mailvotech.integrations.sync.integration_process', MailVotech\IntegrationsBundle\Sync\SyncProcess\Direction\Integration\IntegrationSyncProcess::class);
    $services->set('mailvotech.integrations.sync.internal_process.object_change_generator', MailVotech\IntegrationsBundle\Sync\SyncProcess\Direction\Internal\ObjectChangeGenerator::class);
    $services->set('mailvotech.integrations.sync.internal_process', MailVotech\IntegrationsBundle\Sync\SyncProcess\Direction\Internal\MailVotechSyncProcess::class);
    $services->set('mailvotech.integrations.helper.sync_date', MailVotech\IntegrationsBundle\Sync\Helper\SyncDateHelper::class);
    $services->set('mailvotech.integrations.sync.helper.relations', MailVotech\IntegrationsBundle\Sync\Helper\RelationsHelper::class);
    $services->set('mailvotech.integrations.sync.notifier', MailVotech\IntegrationsBundle\Sync\Notification\Notifier::class);
    $services->set('mailvotech.integrations.sync.notification.writer', MailVotech\IntegrationsBundle\Sync\Notification\Writer::class);
    $services->set('mailvotech.integrations.sync.notification.handler_company', MailVotech\IntegrationsBundle\Sync\Notification\Handler\CompanyNotificationHandler::class)->tag('mailvotech.sync.notification_handler');
    $services->set('mailvotech.integrations.sync.notification.handler_contact', MailVotech\IntegrationsBundle\Sync\Notification\Handler\ContactNotificationHandler::class)->tag('mailvotech.sync.notification_handler');
    $services->set('mailvotech.integrations.sync.notification.helper_company', MailVotech\IntegrationsBundle\Sync\Notification\Helper\CompanyHelper::class);
    $services->set('mailvotech.integrations.sync.notification.helper_user', MailVotech\IntegrationsBundle\Sync\Notification\Helper\UserHelper::class);
    $services->set('mailvotech.integrations.sync.notification.helper_route', MailVotech\IntegrationsBundle\Sync\Notification\Helper\RouteHelper::class);
    $services->set('mailvotech.integrations.sync.notification.helper_user_notification', MailVotech\IntegrationsBundle\Sync\Notification\Helper\UserNotificationHelper::class);
    $services->set('mailvotech.integrations.sync.notification.user_notification_builder', MailVotech\IntegrationsBundle\Sync\Notification\Helper\UserNotificationBuilder::class);
    $services->set('mailvotech.integrations.sync.notification.bulk_notification', MailVotech\IntegrationsBundle\Sync\Notification\BulkNotification::class);
    $services->set('mailvotech.integrations.sync.notification.helper_user_summary_notification', MailVotech\IntegrationsBundle\Sync\Notification\Helper\UserSummaryNotificationHelper::class);

    $services->alias('mailvotech.integrations.repository.field_change', MailVotech\IntegrationsBundle\Entity\FieldChangeRepository::class);
    $services->alias('mailvotech.integrations.repository.object_mapping', MailVotech\IntegrationsBundle\Entity\ObjectMappingRepository::class);
    $services->alias('mailvotech.plugin.integrations.repository.integration', MailVotech\PluginBundle\Entity\IntegrationRepository::class);
    $services->alias('mailvotech.integrations.helper.contact_object', MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectHelper\ContactObjectHelper::class);
    $services->alias('mailvotech.integrations.helper.company_object', MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectHelper\CompanyObjectHelper::class);
    $services->alias('mailvotech.integrations.sync.data_exchange.mailvotech.field_helper', MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Helper\FieldHelper::class);
    $services->alias('mailvotech.integrations.helper.sync_mapping', MailVotech\IntegrationsBundle\Sync\Helper\MappingHelper::class);
    $services->alias('mailvotech.integrations.helper', MailVotech\IntegrationsBundle\Helper\IntegrationsHelper::class);
    $services->alias('mailvotech.integrations.helper.auth_integrations', MailVotech\IntegrationsBundle\Helper\AuthIntegrationsHelper::class);
    $services->alias('mailvotech.integrations.helper.sync_integrations', MailVotech\IntegrationsBundle\Helper\SyncIntegrationsHelper::class);
    $services->alias('mailvotech.integrations.helper.config_integrations', MailVotech\IntegrationsBundle\Helper\ConfigIntegrationsHelper::class);
    $services->alias('mailvotech.integrations.helper.builder_integrations', MailVotech\IntegrationsBundle\Helper\BuilderIntegrationsHelper::class);
    $services->alias('mailvotech.integrations.sync.notification.handler_container', MailVotech\IntegrationsBundle\Sync\Notification\Handler\HandlerContainer::class);
};
