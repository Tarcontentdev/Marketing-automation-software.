<?php

use Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass;
use MailVotech\CoreBundle\Loader\ParameterLoader;
use MailVotech\CoreBundle\Test\EnvLoader;
use Symfony\Component\DependencyInjection\Reference;

/** @var Symfony\Component\DependencyInjection\ContainerBuilder $container */

// Include path settings
$root          = $container->getParameter('mailvotech.application_dir').'/app';
$configBaseDir = ParameterLoader::getLocalConfigBaseDir($root);

$loader->import('config.php');

EnvLoader::load();

// Define some constants from .env
defined('MAILVOTECH_TABLE_PREFIX') || define('MAILVOTECH_TABLE_PREFIX', getenv('MAILVOTECH_DB_PREFIX') ?: '');
defined('MAILVOTECH_ENV') || define('MAILVOTECH_ENV', getenv('MAILVOTECH_ENV') ?: 'test');

// Twig Configuration
$container->loadFromExtension('twig', [
    'cache'            => false,
    'debug'            => '%kernel.debug%',
    'strict_variables' => true,
    'paths'            => [
        '%mailvotech.application_dir%/app/bundles'                  => 'bundles',
        '%mailvotech.application_dir%/app/bundles/CoreBundle'       => 'MailVotechCore',
        '%mailvotech.application_dir%/themes'                       => 'themes',
    ],
    'form_themes' => [
        // Can be found at bundles/CoreBundle/Resources/views/mailvotech_form_layout.html.twig
        '@MailVotechCore/FormTheme/mailvotech_form_layout.html.twig',
    ],
]);

$container->loadFromExtension('framework', [
    'test'    => true,
    'session' => [
        'storage_factory_id' => 'session.storage.factory.mock_file',
        'name'               => 'MOCKSESSION',
    ],
    'profiler' => [
        'collect' => false,
    ],
    'translator' => [
        'enabled' => true,
    ],
    'csrf_protection' => [
        'enabled' => true,
    ],
]);

$container->setParameter('mailvotech.famework.csrf_protection', true);

$container->loadFromExtension('web_profiler', [
    'toolbar'             => false,
    'intercept_redirects' => false,
]);

$connectionSettings = [
    'host'     => '%env(DB_HOST)%' ?: '%mailvotech.db_host%',
    'port'     => '%env(DB_PORT)%' ?: '%mailvotech.db_port%',
    'dbname'   => '%env(DB_NAME)%' ?: '%mailvotech.db_name%',
    'user'     => '%env(DB_USER)%' ?: '%mailvotech.db_user%',
    'password' => '%env(DB_PASSWD)%' ?: '%mailvotech.db_password%',
    'options'  => [PDO::ATTR_STRINGIFY_FETCHES => true], // @see https://www.php.net/manual/en/migration81.incompatible.php#migration81.incompatible.pdo.mysql
];
$container->loadFromExtension('doctrine', [
    'dbal' => [
        'connections' => [
            'default'    => $connectionSettings,
            'unbuffered' => $connectionSettings,
        ],
    ],
]);

$container->setParameter('mailvotech.db_table_prefix', MAILVOTECH_TABLE_PREFIX);

$container->loadFromExtension('monolog', [
    'channels' => [
        'mailvotech',
    ],
    'handlers' => [
        'main' => [
            'formatter' => 'mailvotech.monolog.fulltrace.formatter',
            'type'      => 'rotating_file',
            'path'      => '%kernel.logs_dir%/%kernel.environment%.php',
            'level'     => getenv('MAILVOTECH_DEBUG_LEVEL') ?: 'error',
            'channels'  => [
                '!mailvotech',
            ],
            'max_files' => 7,
        ],
        'console' => [
            'type'   => 'console',
            'bubble' => false,
        ],
        'mailvotech' => [
            'formatter' => 'mailvotech.monolog.fulltrace.formatter',
            'type'      => 'rotating_file',
            'path'      => '%kernel.logs_dir%/mailvotech_%kernel.environment%.php',
            'level'     => getenv('MAILVOTECH_DEBUG_LEVEL') ?: 'error',
            'channels'  => [
                'mailvotech',
            ],
            'max_files' => 7,
        ],
    ],
]);

$container->loadFromExtension('liip_test_fixtures', [
    'cache_db' => [
        'sqlite' => 'liip_functional_test.services_database_backup.sqlite',
    ],
    'keep_database_and_schema' => true,
]);

$loader->import('security_test.php');

// Allow overriding config without a requiring a full bundle or hacks
if (file_exists($configBaseDir.'/config/config_override.php')) {
    $loader->import($configBaseDir.'/config/config_override.php');
}

// Add required parameters
$container->setParameter('mailvotech.secret_key', '68c7e75470c02cba06dd543431411e0de94e04fdf2b3a2eac05957060edb66d0');
$container->setParameter('mailvotech.security.disableUpdates', true);
$container->setParameter('mailvotech.rss_notification_url', null);
$container->setParameter('mailvotech.batch_sleep_time', 0);

// Turn off creating of indexes in lead field fixtures
$container->register('mailvotech.install.fixture.lead_field', MailVotech\InstallBundle\InstallFixtures\ORM\LeadFieldData::class)
    ->addArgument(new Reference('translator'))
    ->addTag(FixturesCompilerPass::FIXTURE_TAG)
    ->setPublic(true);

if (defined('IS_PHPUNIT')) {
    $container->register('security.csrf.token_storage', MailVotech\CoreBundle\Test\Session\InMemoryTokenStorage::class)->setAutowired(true);
}

// Use static namespace for token manager
$container->register('security.csrf.token_manager', Symfony\Component\Security\Csrf\CsrfTokenManager::class)
    ->addArgument(new Reference('security.csrf.token_generator'))
    ->addArgument(new Reference('security.csrf.token_storage'))
    ->addArgument('test')
    ->setPublic(true);

// HTTP client mock handler providing response queue
$container->register(GuzzleHttp\Handler\MockHandler::class)->setPublic(true);

$container->register('http_client', Symfony\Component\HttpClient\MockHttpClient::class)
    ->setPublic(true);

$container->register('test.service_container', MailVotech\CoreBundle\Test\Container\TestContainer::class)
    ->setArgument('$kernel', new Reference('kernel'))
    ->setArgument('$privateServicesLocatorId', 'test.private_services_locator')
    ->setPublic(true);
