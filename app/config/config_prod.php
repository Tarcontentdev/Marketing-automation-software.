<?php

use MailVotech\CoreBundle\Loader\ParameterLoader;

$root          = $container->getParameter('mailvotech.application_dir').'/app';
$configBaseDir = ParameterLoader::getLocalConfigBaseDir($root);

$loader->import('config.php');

if (file_exists($configBaseDir.'/config/security_local.php')) {
    $loader->import($configBaseDir.'/config/security_local.php');
} else {
    $loader->import('security.php');
}

/*
$container->loadFromExtension("framework", array(
    "validation" => array(
        "cache" => "apc"
    )
));
$container->loadFromExtension("doctrine", array(
    "orm" => array(
        "metadata_cache_driver" => "apc",
        "result_cache_driver"   => "apc",
        "query_cache_driver"    => "apc"
    )
));
 */

$container->loadFromExtension('monolog', [
    'channels' => [
        'mailvotech',
    ],
    'handlers' => [
        'main' => [
            'type'         => 'fingers_crossed',
            'buffer_size'  => '200',
            'action_level' => 'error',
            'handler'      => 'nested',
            'channels'     => [
                '!mailvotech',
            ],
        ],
        'nested' => [
            'type'      => 'rotating_file',
            'path'      => '%kernel.logs_dir%/%kernel.environment%.php',
            'level'     => 'error',
            'max_files' => 7,
        ],
        'mailvotech' => [
            'type'      => 'service',
            'id'        => 'mailvotech.monolog.handler',
            'channels'  => [
                'mailvotech',
            ],
        ],
    ],
]);

// Twig Configuration
$container->loadFromExtension('twig', [
    'cache'            => '%env(resolve:MAILVOTECH_TWIG_CACHE_DIR)%',
    'auto_reload'      => true,
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

// Allow overriding config without a requiring a full bundle or hacks
if (file_exists($configBaseDir.'/config/config_override.php')) {
    $loader->import($configBaseDir.'/config/config_override.php');
}

// Allow local settings without committing to git such as swift mailer delivery address overrides
if (file_exists($configBaseDir.'/config/config_local.php')) {
    $loader->import($configBaseDir.'/config/config_local.php');
}
