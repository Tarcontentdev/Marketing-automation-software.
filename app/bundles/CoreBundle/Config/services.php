<?php

declare(strict_types=1);

use MailVotech\CoreBundle\DependencyInjection\MailVotechCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

use Twig\Extra\String\StringExtension;

return function (ContainerConfigurator $configurator): void {
    $parameters = $configurator->parameters();
    $parameters->set('twig.controller.exception.class', MailVotech\CoreBundle\Controller\ExceptionController::class);

    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = [
        'Doctrine',
        'Model/IteratorExportDataModel.php',
        'Form/EventListener/FormExitSubscriber.php',
        'Release',
        'Helper/Chart',
        'Form/DataTransformer',
        'Helper/CommandResponse.php',
        'Helper/Language/Installer.php',
        'Helper/PageHelper.php',
        'Helper/Tree/IntNode.php',
        'Helper/Update/Github/Release.php',
        'Helper/Update/PreUpdateChecks',
        'Predis/Replication/StrategyConfig.php',
        'Predis/Replication/MasterOnlyStrategy.php',
        'ProcessSignal/Exception',
        'ProcessSignal/ProcessSignalState.php',
        'Session/Storage/Handler/RedisSentinelSessionHandler.php',
        'Twig/Helper/ThemeHelper.php',
        'Twig/Helper/MenuHelper.php',
        'Translation/TranslatorLoader.php',
        'Helper/Dsn/Dsn.php',
        'Cache/ResultCacheOptions.php',
    ];

    $services->set(MailVotech\CoreBundle\Twig\Helper\MenuHelper::class)
        ->arg('$helper', \Symfony\Component\DependencyInjection\Loader\Configurator\service('knp_menu.helper'))
        ->tag('twig.helper', ['alias' => 'menu']);

    $services->load('MailVotech\\CoreBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\CoreBundle\\Entity\\', '../Entity/*Repository.php');

    $services->set('mailvotech.helper.core_parameters', MailVotech\CoreBundle\Helper\CoreParametersHelper::class)->tag('twig.helper');

    $services->alias(MailVotech\CoreBundle\Helper\CoreParametersHelper::class, 'mailvotech.helper.core_parameters');
    $services->alias('mailvotech.config', 'mailvotech.helper.core_parameters');

    $services->set('mailvotech.ip_lookup', MailVotech\CoreBundle\IpLookup\AbstractLookup::class)
        ->factory([service('mailvotech.ip_lookup.factory'), 'getService'])
        ->args([param('mailvotech.ip_lookup_service'), param('mailvotech.ip_lookup_auth'), param('mailvotech.ip_lookup_config'), service('mailvotech.http.client')]);
    $services->alias(MailVotech\CoreBundle\IpLookup\AbstractLookup::class, 'mailvotech.ip_lookup');
    $services->set('mailvotech.native.connector', Symfony\Contracts\HttpClient\HttpClientInterface::class)
        ->factory([Symfony\Component\HttpClient\HttpClient::class, 'create']);
    $services->alias(Symfony\Contracts\HttpClient\HttpClientInterface::class, 'mailvotech.native.connector');
    $services->set('mailvotech.translation.loader', MailVotech\CoreBundle\Loader\TranslationLoader::class)->tag('translation.loader', ['alias' => 'mailvotech']);
    $services->alias(MailVotech\CoreBundle\Loader\TranslationLoader::class, 'mailvotech.translation.loader');
    $services->set('mailvotech.helper.theme', MailVotech\CoreBundle\Helper\ThemeHelper::class)
        ->call('setDefaultTheme', [param('mailvotech.theme')]);
    $services->alias(MailVotech\CoreBundle\Helper\ThemeHelper::class, 'mailvotech.helper.theme');
    $services->set('mailvotech.menu_renderer', MailVotech\CoreBundle\Menu\MenuRenderer::class)->tag('knp_menu.renderer', ['alias' => 'mailvotech']);
    $services->alias(MailVotech\CoreBundle\Menu\MenuRenderer::class, 'mailvotech.menu_renderer');

    $services->set('mailvotech.helper.menu', MailVotech\CoreBundle\Menu\MenuHelper::class);
    $services->alias(MailVotech\CoreBundle\Menu\MenuHelper::class, 'mailvotech.helper.menu');
    $services->set('mailvotech.menu.builder', MailVotech\CoreBundle\Menu\MenuBuilder::class);
    $services->alias(MailVotech\CoreBundle\Menu\MenuBuilder::class, 'mailvotech.menu.builder');

    $services->set('mailvotech.helper.twig.date', MailVotech\CoreBundle\Twig\Helper\DateHelper::class)
        ->arg('$dateFullFormat', param('mailvotech.date_format_full'))
        ->arg('$dateShortFormat', param('mailvotech.date_format_short'))
        ->arg('$dateOnlyFormat', param('mailvotech.date_format_dateonly'))
        ->arg('$timeOnlyFormat', param('mailvotech.date_format_timeonly'))
        ->tag('twig.helper', ['alias' => 'date']);
    $services->alias(MailVotech\CoreBundle\Twig\Helper\DateHelper::class, 'mailvotech.helper.twig.date');
    $services->set('mailvotech.helper.twig.gravatar', MailVotech\CoreBundle\Twig\Helper\GravatarHelper::class)->tag('twig.helper', ['alias' => 'gravatar']);
    $services->alias(MailVotech\CoreBundle\Twig\Helper\GravatarHelper::class, 'mailvotech.helper.twig.gravatar');
    $services->set('mailvotech.helper.twig.analytics', MailVotech\CoreBundle\Twig\Helper\AnalyticsHelper::class)->tag('twig.helper', ['alias' => 'analytics']);
    $services->alias(MailVotech\CoreBundle\Twig\Helper\AnalyticsHelper::class, 'mailvotech.helper.twig.analytics');
    $services->set('mailvotech.helper.twig.config', MailVotech\CoreBundle\Twig\Helper\ConfigHelper::class)->tag('twig.helper', ['alias' => 'config']);
    $services->alias(MailVotech\CoreBundle\Twig\Helper\ConfigHelper::class, 'mailvotech.helper.twig.config');
    $services->set('mailvotech.helper.twig.mautibot', MailVotech\CoreBundle\Twig\Helper\MautibotHelper::class)->tag('twig.helper', ['alias' => 'mautibot']);
    $services->alias(MailVotech\CoreBundle\Twig\Helper\MautibotHelper::class, 'mailvotech.helper.twig.mautibot');
    $services->set('mailvotech.helper.twig.button', MailVotech\CoreBundle\Twig\Helper\ButtonHelper::class)->tag('twig.helper', ['alias' => 'buttons']);
    $services->alias(MailVotech\CoreBundle\Twig\Helper\ButtonHelper::class, 'mailvotech.helper.twig.button');
    $services->set('mailvotech.helper.twig.content', MailVotech\CoreBundle\Twig\Helper\ContentHelper::class)->tag('twig.helper', ['alias' => 'content']);
    $services->alias(MailVotech\CoreBundle\Twig\Helper\ContentHelper::class, 'mailvotech.helper.twig.content');
    $services->set('mailvotech.helper.twig.formatter', MailVotech\CoreBundle\Twig\Helper\FormatterHelper::class)->tag('twig.helper', ['alias' => 'formatter']);
    $services->alias(MailVotech\CoreBundle\Twig\Helper\FormatterHelper::class, 'mailvotech.helper.twig.formatter');
    $services->set('mailvotech.helper.twig.version', MailVotech\CoreBundle\Twig\Helper\VersionHelper::class)->tag('twig.helper', ['alias' => 'version']);
    $services->alias(MailVotech\CoreBundle\Twig\Helper\VersionHelper::class, 'mailvotech.helper.twig.version');
    $services->set('mailvotech.helper.twig.security', MailVotech\CoreBundle\Twig\Helper\SecurityHelper::class)->tag('twig.helper', ['alias' => 'security']);
    $services->alias(MailVotech\CoreBundle\Twig\Helper\SecurityHelper::class, 'mailvotech.helper.twig.security');

    $services->set('mailvotech.core.service.local_file_adapter', MailVotech\CoreBundle\Service\LocalFileAdapterService::class)
        ->arg('$root', param('env(resolve:MAILVOTECH_EL_FINDER_PATH)'));

    $services->alias(MailVotech\CoreBundle\Service\LocalFileAdapterService::class, 'mailvotech.core.service.local_file_adapter');
    $services->set('mailvotech.helper.maxmind_do_not_sell_download', MailVotech\CoreBundle\Helper\MaxMindDoNotSellDownloadHelper::class)
        ->arg('$auth', param('mailvotech.ip_lookup_auth'));
    $services->alias(MailVotech\CoreBundle\Helper\MaxMindDoNotSellDownloadHelper::class, 'mailvotech.helper.maxmind_do_not_sell_download');
    $services->set('mailvotech.cache.warmer.middleware', MailVotech\CoreBundle\Cache\MiddlewareCacheWarmer::class)
        ->arg('$env', param('kernel.environment'))
        ->tag('kernel.cache_warmer');
    $services->alias(MailVotech\CoreBundle\Cache\MiddlewareCacheWarmer::class, 'mailvotech.cache.warmer.middleware');
    $services->set('mailvotech.helper.cache_storage', MailVotech\CoreBundle\Helper\CacheStorageHelper::class)
        ->arg('$adaptor', 'db')
        ->arg('$namespace', param('mailvotech.db_table_prefix'))
        ->arg('$connection', service('doctrine.dbal.default_connection'))
        ->arg('$cacheDir', param('kernel.cache_dir'));
    $services->alias(MailVotech\CoreBundle\Helper\CacheStorageHelper::class, 'mailvotech.helper.cache_storage');
    $services->set('mailvotech.helper.cache', MailVotech\CoreBundle\Helper\CacheHelper::class)
        ->arg('$cacheDir', param('kernel.cache_dir'));
    $services->alias(MailVotech\CoreBundle\Helper\CacheHelper::class, 'mailvotech.helper.cache');
    $services->set('mailvotech.ip_lookup.factory', MailVotech\CoreBundle\Factory\IpLookupFactory::class)
        ->arg('$lookupServices', param('mailvotech.ip_lookup_services'))
        ->arg('$cacheDir', param('kernel.cache_dir'));
    $services->alias(MailVotech\CoreBundle\Factory\IpLookupFactory::class, 'mailvotech.ip_lookup.factory');
    $services->set('mailvotech.schema.helper.column', MailVotech\CoreBundle\Doctrine\Helper\ColumnSchemaHelper::class)
        ->arg('$prefix', param('mailvotech.db_table_prefix'));
    $services->alias(MailVotech\CoreBundle\Doctrine\Helper\ColumnSchemaHelper::class, 'mailvotech.schema.helper.column');
    $services->set('mailvotech.schema.helper.index', MailVotech\CoreBundle\Doctrine\Helper\IndexSchemaHelper::class)
        ->arg('$prefix', param('mailvotech.db_table_prefix'));
    $services->alias(MailVotech\CoreBundle\Doctrine\Helper\IndexSchemaHelper::class, 'mailvotech.schema.helper.index');
    $services->set('mailvotech.schema.helper.table', MailVotech\CoreBundle\Doctrine\Helper\TableSchemaHelper::class)
        ->arg('$prefix', param('mailvotech.db_table_prefix'));
    $services->alias(MailVotech\CoreBundle\Doctrine\Helper\TableSchemaHelper::class, 'mailvotech.schema.helper.table');
    $services->set('mailvotech.maxmind.doNotSellList', MailVotech\CoreBundle\IpLookup\DoNotSellList\MaxMindDoNotSellList::class);
    $services->alias(MailVotech\CoreBundle\IpLookup\DoNotSellList\MaxMindDoNotSellList::class, 'mailvotech.maxmind.doNotSellList');
    $services->set('mailvotech.form.type.dynamic_content_filter_entry_filters', MailVotech\CoreBundle\Form\Type\DynamicContentFilterEntryFiltersType::class)
        ->call('setConnection', [service('database_connection')]);
    $services->alias(MailVotech\CoreBundle\Form\Type\DynamicContentFilterEntryFiltersType::class, 'mailvotech.form.type.dynamic_content_filter_entry_filters');

    $services->set('mailvotech.core.subscriber.router', MailVotech\CoreBundle\EventListener\RouterSubscriber::class)
        ->arg('$scheme', param('router.request_context.scheme'))
        ->arg('$host', param('router.request_context.host'))
        ->arg('$httpsPort', param('request_listener.https_port'))
        ->arg('$httpPort', param('request_listener.http_port'))
        ->arg('$baseUrl', param('router.request_context.base_url'));
    $services->alias(MailVotech\CoreBundle\EventListener\RouterSubscriber::class, 'mailvotech.core.subscriber.router');
    $services->set('mailvotech.helper.paths', MailVotech\CoreBundle\Helper\PathsHelper::class)
        ->arg('$cacheDir', param('kernel.cache_dir'))
        ->arg('$logsDir', param('kernel.logs_dir'))
        ->arg('$rootDir', param('mailvotech.application_dir'));
    $services->alias(MailVotech\CoreBundle\Helper\PathsHelper::class, 'mailvotech.helper.paths');
    $services->set('mailvotech.helper.bundle', MailVotech\CoreBundle\Helper\BundleHelper::class)
        ->arg('$coreBundles', param('mailvotech.bundles'))
        ->arg('$pluginBundles', param('mailvotech.plugin.bundles'));
    $services->alias(MailVotech\CoreBundle\Helper\BundleHelper::class, 'mailvotech.helper.bundle');
    $services->set('mailvotech.configurator', MailVotech\CoreBundle\Configurator\Configurator::class);
    $services->alias(MailVotech\CoreBundle\Configurator\Configurator::class, 'mailvotech.configurator');
    $services->set('mailvotech.cipher.openssl', MailVotech\CoreBundle\Security\Cryptography\Cipher\Symmetric\OpenSSLCipher::class);
    $services->alias(MailVotech\CoreBundle\Security\Cryptography\Cipher\Symmetric\OpenSSLCipher::class, 'mailvotech.cipher.openssl');
    $services->set('mailvotech.security', MailVotech\CoreBundle\Security\Permissions\CorePermissions::class)
        ->arg('$bundles', param('mailvotech.bundles'))
        ->arg('$pluginBundles', param('mailvotech.plugin.bundles'));
    $services->alias(MailVotech\CoreBundle\Security\Permissions\CorePermissions::class, 'mailvotech.security');

    $services->set('mailvotech.exception.listener', MailVotech\CoreBundle\EventListener\ExceptionListener::class)
        ->arg('$controller', 'MailVotech\CoreBundle\Controller\ExceptionController::showAction');

    $services->alias(MailVotech\CoreBundle\EventListener\ExceptionListener::class, 'mailvotech.exception.listener');
    $services->set('mailvotech.helper.cookie', MailVotech\CoreBundle\Helper\CookieHelper::class)
        ->arg('$path', param('mailvotech.cookie_path'))
        ->arg('$domain', param('mailvotech.cookie_domain'))
        ->arg('$secure', param('mailvotech.cookie_secure'))
        ->arg('$httponly', param('mailvotech.cookie_httponly'))
        ->tag('kernel.event_subscriber');
    $services->alias(MailVotech\CoreBundle\Helper\CookieHelper::class, 'mailvotech.helper.cookie');

    $services->set(MailVotech\CoreBundle\Helper\EncryptionHelper::class)
        ->args([
            service('mailvotech.helper.core_parameters'),
            service('mailvotech.cipher.openssl'),
        ]);

    $services->set('mailvotech.form.list.validator.circular', MailVotech\CoreBundle\Form\Validator\Constraints\CircularDependencyValidator::class)->tag('validator.constraint_validator');
    $services->alias(MailVotech\CoreBundle\Form\Validator\Constraints\CircularDependencyValidator::class, 'mailvotech.form.list.validator.circular');

    $services->alias('mailvotech.helper.file_uploader', MailVotech\CoreBundle\Helper\FileUploader::class);
    $services->alias('mailvotech.helper.file_path_resolver', MailVotech\CoreBundle\Helper\FilePathResolver::class);
    $services->alias('mailvotech.helper.file_properties', MailVotech\CoreBundle\Helper\FileProperties::class);
    $services->alias('mailvotech.core.validator.file_upload', MailVotech\CoreBundle\Validator\FileUploadValidator::class);
    $services->alias('mailvotech.filesystem', MailVotech\CoreBundle\Helper\Filesystem::class);

    /* @deprecated to be removed in MailVotech 4. Use 'mailvotech.filesystem' instead. */
    $services->set('symfony.filesystem', Symfony\Component\Filesystem\Filesystem::class);
    $services->alias(Symfony\Component\Filesystem\Filesystem::class, 'symfony.filesystem');

    $services->set('symfony.finder', Symfony\Component\Finder\Finder::class);
    $services->alias(Symfony\Component\Finder\Finder::class, 'symfony.finder');

    $services->alias('mailvotech.helper.input_helper', MailVotech\CoreBundle\Helper\InputHelper::class);
    $services->alias('mailvotech.helper.trailing_slash', MailVotech\CoreBundle\Helper\TrailingSlashHelper::class);
    $services->alias('mailvotech.helper.url', MailVotech\CoreBundle\Helper\UrlHelper::class);
    $services->alias('mailvotech.helper.hash', MailVotech\CoreBundle\Helper\HashHelper\HashHelper::class);
    $services->alias('mailvotech.helper.random', MailVotech\CoreBundle\Helper\RandomHelper\RandomHelper::class);
    $services->alias('mailvotech.helper.phone_number', MailVotech\CoreBundle\Helper\PhoneNumberHelper::class);
    $services->set(MailVotech\CoreBundle\Loader\RouteLoader::class)
        ->tag('routing.loader');

    $services->set(MailVotech\CoreBundle\Doctrine\Provider\VersionProvider::class);
    $services->set(MailVotech\CoreBundle\Doctrine\Provider\GeneratedColumnsProvider::class);

    $services->get(MailVotech\CoreBundle\EventListener\DoctrineGeneratedColumnsListener::class)
        ->arg('$logger', \Symfony\Component\DependencyInjection\Loader\Configurator\service('monolog.logger.mailvotech'))
        ->tag('doctrine.event_listener', ['event' => 'postGenerateSchema', 'lazy' => true]);
    $services->alias('mailvotech.generated.columns.doctrine.listener', MailVotech\CoreBundle\EventListener\DoctrineGeneratedColumnsListener::class);

    $services->set(MailVotech\CoreBundle\Doctrine\Loader\MailVotechFixturesLoader::class)
        ->arg('$fixturesLoader', \Symfony\Component\DependencyInjection\Loader\Configurator\service('doctrine.fixtures.loader'));

    $services->get(MailVotech\CoreBundle\EventListener\ErrorHandlingListener::class)
        ->arg('$logger', \Symfony\Component\DependencyInjection\Loader\Configurator\service('monolog.logger.mailvotech'))
        ->arg('$mainLogger', \Symfony\Component\DependencyInjection\Loader\Configurator\service('monolog.logger'))
        ->arg('$debugLogger', \Symfony\Component\DependencyInjection\Loader\Configurator\expr("container.has('monolog.logger.chrome') ? container.get('monolog.logger.chrome') : null"));
    $services->alias('mailvotech.core.errorhandler.subscriber', MailVotech\CoreBundle\EventListener\ErrorHandlingListener::class);

    $services->get(MailVotech\CoreBundle\Helper\UpdateHelper::class)
        ->arg('$logger', \Symfony\Component\DependencyInjection\Loader\Configurator\service('monolog.logger.mailvotech'));
    $services->alias('mailvotech.helper.update', MailVotech\CoreBundle\Helper\UpdateHelper::class);
    $services->alias('mailvotech.helper.update.release_parser', MailVotech\CoreBundle\Helper\Update\Github\ReleaseParser::class);

    $services->get(MailVotech\CoreBundle\Helper\ComposerHelper::class)
        ->arg('$logger', \Symfony\Component\DependencyInjection\Loader\Configurator\service('monolog.logger.mailvotech'));
    $services->alias('mailvotech.helper.composer', MailVotech\CoreBundle\Helper\ComposerHelper::class);

    $services->get(MailVotech\CoreBundle\Update\Step\DeleteCacheStep::class)->tag('mailvotech.update_step');
    $services->alias('mailvotech.update.step.delete_cache', MailVotech\CoreBundle\Update\Step\DeleteCacheStep::class);

    $services->get(MailVotech\CoreBundle\Update\Step\FinalizeUpdateStep::class)->tag('mailvotech.update_step');
    $services->alias('mailvotech.update.step.finalize', MailVotech\CoreBundle\Update\Step\FinalizeUpdateStep::class);

    $services->get(MailVotech\CoreBundle\Update\Step\InstallNewFilesStep::class)->tag('mailvotech.update_step');
    $services->alias('mailvotech.update.step.install_new_files', MailVotech\CoreBundle\Update\Step\InstallNewFilesStep::class);

    $services->get(MailVotech\CoreBundle\Update\Step\RemoveDeletedFilesStep::class)
        ->arg('$logger', \Symfony\Component\DependencyInjection\Loader\Configurator\service('monolog.logger.mailvotech'))
        ->tag('mailvotech.update_step');
    $services->alias('mailvotech.update.step.remove_deleted_files', MailVotech\CoreBundle\Update\Step\RemoveDeletedFilesStep::class);

    $services->get(MailVotech\CoreBundle\Update\Step\UpdateSchemaStep::class)->tag('mailvotech.update_step');
    $services->alias('mailvotech.update.step.update_schema', MailVotech\CoreBundle\Update\Step\UpdateSchemaStep::class);

    $services->get(MailVotech\CoreBundle\Update\Step\UpdateTranslationsStep::class)
        ->arg('$logger', \Symfony\Component\DependencyInjection\Loader\Configurator\service('monolog.logger.mailvotech'))
        ->tag('mailvotech.update_step');
    $services->alias('mailvotech.update.step.update_translations', MailVotech\CoreBundle\Update\Step\UpdateTranslationsStep::class);

    $services->get(MailVotech\CoreBundle\Update\Step\PreUpdateChecksStep::class)->tag('mailvotech.update_step');
    $services->alias('mailvotech.update.step.checks', MailVotech\CoreBundle\Update\Step\PreUpdateChecksStep::class);

    $services->set(MailVotech\CoreBundle\Helper\Update\PreUpdateChecks\CheckPhpVersion::class)->tag('mailvotech.update_check');
    $services->alias('mailvotech.update.checks.php', MailVotech\CoreBundle\Helper\Update\PreUpdateChecks\CheckPhpVersion::class);

    $services->set(MailVotech\CoreBundle\Helper\Update\PreUpdateChecks\CheckDatabaseDriverAndVersion::class)->tag('mailvotech.update_check');
    $services->alias('mailvotech.update.checks.database', MailVotech\CoreBundle\Helper\Update\PreUpdateChecks\CheckDatabaseDriverAndVersion::class);
    $services->alias('mailvotech.core.service.bulk_notification', MailVotech\CoreBundle\Service\BulkNotification::class);

    $services->get(MailVotech\CoreBundle\Monolog\LogProcessor::class)->tag('monolog.processor');
    $services->alias('mailvotech.core.service.log_processor', MailVotech\CoreBundle\Monolog\LogProcessor::class);

    $services->get(MailVotech\CoreBundle\Monolog\Handler\FileLogHandler::class)
        ->arg('$exceptionFormatter', \Symfony\Component\DependencyInjection\Loader\Configurator\service('mailvotech.monolog.fulltrace.formatter'));
    $services->alias('mailvotech.monolog.handler', MailVotech\CoreBundle\Monolog\Handler\FileLogHandler::class);

    $services->set(MailVotech\CoreBundle\DependencyInjection\EnvProcessor\NullableProcessor::class)
        ->tag('container.env_var_processor');
    $services->alias('mailvotech.di.env_processor.nullable', MailVotech\CoreBundle\DependencyInjection\EnvProcessor\NullableProcessor::class);

    $services->set(MailVotech\CoreBundle\DependencyInjection\EnvProcessor\IntNullableProcessor::class)
        ->tag('container.env_var_processor');

    $services->alias('mailvotech.di.env_processor.int_nullable', MailVotech\CoreBundle\DependencyInjection\EnvProcessor\IntNullableProcessor::class);

    $services->set(MailVotech\CoreBundle\DependencyInjection\EnvProcessor\MailVotechConstProcessor::class)
        ->tag('container.env_var_processor');

    $services->alias('mailvotech.di.env_processor.mailvotechconst', MailVotech\CoreBundle\DependencyInjection\EnvProcessor\MailVotechConstProcessor::class);

    $services->alias('mailvotech.helper.user', MailVotech\CoreBundle\Helper\UserHelper::class);
    $services->alias('mailvotech.helper.ip_lookup', MailVotech\CoreBundle\Helper\IpLookupHelper::class);
    $services->alias('mailvotech.helper.token_builder', MailVotech\CoreBundle\Helper\BuilderTokenHelper::class);
    $services->alias('mailvotech.helper.token_builder.factory', MailVotech\CoreBundle\Helper\BuilderTokenHelperFactory::class);
    $services->alias('mailvotech.helper.app_version', MailVotech\CoreBundle\Helper\AppVersion::class);
    $services->alias('mailvotech.helper.command', MailVotech\CoreBundle\Helper\CommandHelper::class);
    $services->alias('mailvotech.page.helper.factory', MailVotech\CoreBundle\Factory\PageHelperFactory::class);

    $services->alias('mailvotech.core.repository.ip_address', MailVotech\CoreBundle\Entity\IpAddressRepository::class);

    // Explicitly register our Twig extension with high priority
    $services->set(MailVotech\CoreBundle\Twig\Extension\OverrideIncludeExtension::class)
        ->autowire()
        ->tag('twig.extension', ['priority' => 100]);

    $services->get(MailVotech\CoreBundle\Twig\Extension\FormExtension::class)
        ->arg('$formRenderer', \Symfony\Component\DependencyInjection\Loader\Configurator\service('twig.form.renderer'));

    $services->set('mailvotech.http.client', GuzzleHttp\Client::class)->autowire();
    $services->set(MailVotech\CoreBundle\Doctrine\MigrationFactoryDecorator::class)->autowire();

    $services->set(StringExtension::class)
        ->tag('twig.extension');

    $services->alias(GuzzleHttp\Client::class, 'mailvotech.http.client');
    $services->alias(Psr\Http\Client\ClientInterface::class, 'mailvotech.http.client');
    $services->alias(Symfony\Component\DependencyInjection\ContainerInterface::class, 'service_container');
    $services->alias(Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface::class, 'argument_resolver');

    $services->alias(MailVotech\CoreBundle\Doctrine\Provider\VersionProviderInterface::class, MailVotech\CoreBundle\Doctrine\Provider\VersionProvider::class);
    $services->alias('mailvotech.model.factory', MailVotech\CoreBundle\Factory\ModelFactory::class);
    $services->alias('twig.helper.assets', MailVotech\CoreBundle\Twig\Helper\AssetsHelper::class);
    $services->alias('transifex.factory', MailVotech\CoreBundle\Factory\TransifexFactory::class);
    $services->alias('mailvotech.helper.language', MailVotech\CoreBundle\Helper\LanguageHelper::class);
    $services->alias('mailvotech.helper.email.address', MailVotech\CoreBundle\Helper\EmailAddressHelper::class);
    $services->alias('mailvotech.helper.assetgeneration', MailVotech\CoreBundle\Helper\AssetGenerationHelper::class);
    $services->alias('mailvotech.helper.update_checks', MailVotech\CoreBundle\Helper\PreUpdateCheckHelper::class);
    $services->alias('mailvotech.update.step_provider', MailVotech\CoreBundle\Update\StepProvider::class);

    $services->get(MailVotech\CoreBundle\Twig\Helper\AssetsHelper::class)->tag('twig.helper', ['alias' => 'assets']);

    $services->get(MailVotech\CoreBundle\Model\NotificationModel::class)->call('setDisableUpdates', ['%mailvotech.security.disableUpdates%']);
    $services->alias('mailvotech.core.model.auditlog', MailVotech\CoreBundle\Model\AuditLogModel::class);
    $services->alias('mailvotech.core.model.notification', MailVotech\CoreBundle\Model\NotificationModel::class);
    $services->alias('mailvotech.core.model.form', MailVotech\CoreBundle\Model\FormModel::class);
};
