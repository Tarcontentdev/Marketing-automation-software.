<?php

declare(strict_types=1);

return [
    'menu' => [
        'admin' => [
            'mailvotech.user_management' => [
                'id'        => 'mailvotech_user_management_root',
                'priority'  => 17,
                'access'    => ['user:users:view', 'user:roles:view'],
            ],
            'mailvotech.user.users' => [
                'access'    => 'user:users:view',
                'route'     => 'mailvotech_user_index',
                'parent'    => 'mailvotech.user_management',
                'iconClass' => 'ri-user-settings-line',
            ],
            'mailvotech.user.roles' => [
                'access'    => 'user:roles:view',
                'route'     => 'mailvotech_role_index',
                'parent'    => 'mailvotech.user_management',
                'iconClass' => 'ri-shield-user-line',
            ],
        ],
    ],

    'routes' => [
        'main' => [
            'login' => [
                'path'       => '/login',
                'controller' => 'MailVotech\UserBundle\Controller\SecurityController::loginAction',
            ],
            'mailvotech_user_logincheck' => [
                'path'       => '/login_check',
                'controller' => 'MailVotech\UserBundle\Controller\SecurityController::loginCheckAction',
            ],
            'mailvotech_user_logout' => [
                'path' => '/logout',
            ],
            'mailvotech_sso_login' => [
                'path'       => '/sso_login/{integration}',
                'controller' => 'MailVotech\UserBundle\Controller\SecurityController::ssoLoginAction',
            ],
            'mailvotech_sso_login_check' => [
                'path'       => '/sso_login_check/{integration}',
                'controller' => 'MailVotech\UserBundle\Controller\SecurityController::ssoLoginCheckAction',
            ],
            'lightsaml_sp.login' => [
                'path'       => '/saml/login',
                'controller' => 'LightSaml\SpBundle\Controller\DefaultController::loginAction',
            ],
            'lightsaml_sp.login_check' => [
                'path' => '/saml/login_check',
            ],
            'mailvotech_user_index' => [
                'path'       => '/users/{page}',
                'controller' => 'MailVotech\UserBundle\Controller\UserController::indexAction',
            ],
            'mailvotech_user_action' => [
                'path'       => '/users/{objectAction}/{objectId}',
                'controller' => 'MailVotech\UserBundle\Controller\UserController::executeAction',
            ],
            'mailvotech_role_index' => [
                'path'       => '/roles/{page}',
                'controller' => 'MailVotech\UserBundle\Controller\RoleController::indexAction',
            ],
            'mailvotech_role_action' => [
                'path'       => '/roles/{objectAction}/{objectId}',
                'controller' => 'MailVotech\UserBundle\Controller\RoleController::executeAction',
            ],
            'mailvotech_user_account' => [
                'path'       => '/account',
                'controller' => 'MailVotech\UserBundle\Controller\ProfileController::indexAction',
            ],
        ],

        'api' => [
            'mailvotech_api_usersstandard' => [
                'standard_entity' => true,
                'name'            => 'users',
                'path'            => '/users',
                'controller'      => MailVotech\UserBundle\Controller\Api\UserApiController::class,
            ],
            'mailvotech_api_getself' => [
                'path'       => '/users/self',
                'controller' => 'MailVotech\UserBundle\Controller\Api\UserApiController::getSelfAction',
            ],
            'mailvotech_api_checkpermission' => [
                'path'       => '/users/{id}/permissioncheck',
                'controller' => 'MailVotech\UserBundle\Controller\Api\UserApiController::isGrantedAction',
                'method'     => 'POST',
            ],
            'mailvotech_api_getuserroles' => [
                'path'       => '/users/list/roles',
                'controller' => 'MailVotech\UserBundle\Controller\Api\UserApiController::getRolesAction',
            ],
            'mailvotech_api_rolesstandard' => [
                'standard_entity' => true,
                'name'            => 'roles',
                'path'            => '/roles',
                'controller'      => MailVotech\UserBundle\Controller\Api\RoleApiController::class,
            ],
        ],
        'public' => [
            'mailvotech_user_passwordreset' => [
                'path'       => '/passwordreset',
                'controller' => 'MailVotech\UserBundle\Controller\PublicController::passwordResetAction',
            ],
            'mailvotech_user_passwordresetconfirm' => [
                'path'       => '/passwordresetconfirm',
                'controller' => 'MailVotech\UserBundle\Controller\PublicController::passwordResetConfirmAction',
            ],
            'mailvotech_user_invite_register' => [
                'path'       => '/invite/{token}',
                'controller' => 'MailVotech\UserBundle\Controller\PublicController::inviteAction',
            ],
            'lightsaml_sp.metadata' => [
                'path'       => '/saml/metadata.xml',
                'controller' => 'LightSaml\SpBundle\Controller\DefaultController::metadataAction',
            ],
            'lightsaml_sp.discovery' => [
                'path'       => '/saml/discovery',
                'controller' => 'LightSaml\SpBundle\Controller\DefaultController::discoveryAction',
            ],
            'mailvotech_saml_login_retry' => [
                'path'       => '/saml/login_retry',
                'controller' => 'MailVotech\UserBundle\Controller\SecurityController::samlLoginRetryAction',
            ],
        ],
    ],
    'parameters' => [
        'saml_idp_metadata'            => '',
        'saml_idp_entity_id'           => '',
        'saml_idp_own_certificate'     => '',
        'saml_idp_own_private_key'     => '',
        'saml_idp_own_password'        => '',
        'saml_idp_email_attribute'     => '',
        'saml_idp_username_attribute'  => '',
        'saml_idp_firstname_attribute' => '',
        'saml_idp_lastname_attribute'  => '',
        'saml_idp_default_role'        => '',
    ],
];
