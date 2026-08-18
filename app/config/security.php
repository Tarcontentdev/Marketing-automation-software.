<?php

use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

$firewalls = [
    'install' => [
        'pattern'  => '^/installer',
        'lazy'     => true,
        'context'  => 'mailvotech',
        'security' => false,
    ],
    'dev' => [
        'pattern'  => '^/(_(profiler|wdt)|css|images|js)/',
        'security' => true,
        'lazy'     => true,
    ],
    'login' => [
        'pattern' => '^/s/login$',
        'lazy'    => true,
        'context' => 'mailvotech',
    ],
    'sso_login' => [
        'pattern'            => '^/s/sso_login',
        'lazy'               => true,
        'mailvotech_plugin_auth' => true,
        'context'            => 'mailvotech',
    ],
    'saml_login' => [
        'pattern' => '^/s/saml/login$',
        'lazy'    => true,
        'context' => 'mailvotech',
    ],
    'saml_discovery' => [
        'pattern' => '^/saml/discovery$',
        'lazy'    => true,
        'context' => 'mailvotech',
    ],
    'oauth2_token' => [
        'pattern'  => '^/oauth/v2/token',
        'security' => false,
    ],
    'oauth2_area' => [
        'pattern'    => '^/oauth/v2/authorize',
        'form_login' => [
            'provider'   => 'user_provider',
            'check_path' => '/oauth/v2/authorize_login_check',
            'login_path' => '/oauth/v2/authorize_login',
        ],
        'lazy' => true,
    ],
    'v2api' => [
        'pattern'            => '^/api/v2',
        'fos_oauth'          => true,
        'mailvotech_plugin_auth' => true,
        'http_basic'         => true,
        'context'            => 'mailvotech',
        'provider'           => 'user_provider',
        'entry_point'        => 'fos_oauth_server.security.entry_point',
    ],
    'api' => [
        'pattern'            => '^/api/',
        'fos_oauth'          => true,
        'mailvotech_plugin_auth' => true,
        'stateless'          => true,
        'http_basic'         => true,
        'entry_point'        => 'fos_oauth_server.security.entry_point',
    ],
    'main' => [
        'pattern'       => '^/(s/|elfinder|efconnect)',
        'light_saml_sp' => [
            'provider'        => 'user_provider',
            'success_handler' => 'mailvotech.security.authentication_handler',
            'failure_handler' => 'mailvotech.security.authentication_handler',
            'user_creator'    => 'mailvotech.security.saml.user_creator',
            'username_mapper' => 'mailvotech.security.saml.username_mapper',

            // If saml is disabled, these still must contain a proper saml login URLs.
            // Otherwise, this prevents handling of the
            // \LightSaml\SpBundle\Security\Http\Authenticator\SamlServiceProviderAuthenticator::supports
            'login_path'      => '%env(MAILVOTECH_SAML_LOGIN_PATH)%', // '/s/saml/login',
            'check_path'      => '%env(MAILVOTECH_SAML_LOGIN_CHECK_PATH)%', // '/s/saml/login_check',
        ],
        'form_login' => [
            'enable_csrf'     => true,
            'success_handler' => 'mailvotech.security.authentication_handler',
            'failure_handler' => 'mailvotech.security.authentication_handler',
            'login_path'      => '/s/login',
            'check_path'      => '/s/login_check',
        ],
        'logout' => [
            'path'   => '/s/logout',
            'target' => '/s/login',
        ],
        'remember_me' => [
            'secret'   => '%mailvotech.rememberme_key%',
            'lifetime' => '%mailvotech.rememberme_lifetime%',
            'path'     => '%mailvotech.rememberme_path%',
            'domain'   => '%mailvotech.rememberme_domain%',
            'samesite' => 'lax',
        ],
        'entry_point'      => MailVotech\UserBundle\Security\EntryPoint\MainEntryPoint::class,
        'mailvotech_sso'       => [], // options are copied from `form_login` in \MailVotech\UserBundle\DependencyInjection\Firewall\Factory\MailVotechSsoFactory
        'fos_oauth'        => true,
        'context'          => 'mailvotech',
        'login_throttling' => [
            'max_attempts' => 3,
            'interval'     => '30 minutes',
        ],
    ],
    'public' => [
        'pattern' => '^/',
        'lazy'    => true,
        'context' => 'mailvotech',
    ],
];

if (!$container->getParameter('mailvotech.famework.csrf_protection')) {
    unset($firewalls['main']['simple_form']['csrf_token_generator']);
}

$container->loadFromExtension(
    'security',
    [
        'providers' => [
            'user_provider' => [
                'id' => 'mailvotech.user.provider',
            ],
        ],
        'password_hashers' => [
            Symfony\Component\Security\Core\User\UserInterface::class => [
                'algorithm'  => 'bcrypt',
                'iterations' => 12,
            ],
            MailVotech\UserBundle\Entity\User::class => [
                'algorithm'  => 'bcrypt',
                'iterations' => 12,
            ],
        ],
        'role_hierarchy' => [
            'ROLE_ADMIN' => 'ROLE_USER',
        ],
        'firewalls'      => $firewalls,
        'access_control' => [
            // First there should be URIs for login or definitely public ones.
            ['path' => '^/installer', 'roles' => AuthenticatedVoter::PUBLIC_ACCESS],
            ['path' => '^/(_(profiler|wdt)|css|images|js)/', 'roles' => AuthenticatedVoter::PUBLIC_ACCESS],
            ['path' => '^/s/login$', 'roles' => AuthenticatedVoter::PUBLIC_ACCESS],
            ['path' => '^/s/sso_login', 'roles' => AuthenticatedVoter::PUBLIC_ACCESS],
            ['path' => '^/s/saml/login$', 'roles' => AuthenticatedVoter::PUBLIC_ACCESS],
            ['path' => '^/saml/discovery$', 'roles' => AuthenticatedVoter::PUBLIC_ACCESS],
            ['path' => '^/oauth/v2/authorize', 'roles' => AuthenticatedVoter::PUBLIC_ACCESS],
            // Second should be URIs that are defined as non-public.
            ['path' => '^/api', 'roles' => AuthenticatedVoter::IS_AUTHENTICATED_FULLY],
            ['path' => '^/(s/|elfinder|efconnect)', 'roles' => AuthenticatedVoter::IS_AUTHENTICATED],
            // Last the URIs that are none of the above.
            ['path' => '^/', 'roles' => AuthenticatedVoter::PUBLIC_ACCESS],
        ],
    ]
);

$container->setParameter('mailvotech.saml_idp_entity_id', '%env(MAILVOTECH_SAML_ENTITY_ID)%');
$container->setParameter('mailvotech.saml_enabled', '%env(bool:MAILVOTECH_SAML_ENABLED)%');
$container->loadFromExtension(
    'light_saml_symfony_bridge',
    [
        'own' => [
            'entity_descriptor_provider' => [
                'id' => 'mailvotech.security.saml.entity_descriptor_provider',
            ],
            'entity_id' => '%mailvotech.saml_idp_entity_id%',
        ],
        'store' => [
            'id_state' => 'mailvotech.security.saml.id_store',
            'request'  => MailVotech\UserBundle\Security\SAML\Store\Request\RequestStateStore::class,
        ],
    ]
);

$loader->import('security_api.php');

// List config keys we do not want the user to change via the config UI
$restrictedConfigFields = [
    'db_driver',
    'db_host',
    'db_table_prefix',
    'db_name',
    'db_user',
    'db_password',
    'db_path',
    'db_port',
    'secret_key',
];

// List config keys that are dev mode only
if ('prod' == $container->getParameter('kernel.environment')) {
    $restrictedConfigFields = array_merge($restrictedConfigFields, ['transifex_username', 'transifex_password']);
}

$container->setParameter('mailvotech.security.restrictedConfigFields', $restrictedConfigFields);
$container->setParameter('mailvotech.security.restrictedConfigFields.displayMode', MailVotech\ConfigBundle\Form\Helper\RestrictionHelper::MODE_REMOVE);

/*
 * Optional security parameters
 * mailvotech.security.disableUpdates = disables remote checks for updates
 * mailvotech.security.restrictedConfigFields.displayMode = accepts either remove or mask; mask will disable the input with a "Set by system" message
 */
$container->setParameter('mailvotech.security.disableUpdates', false);
