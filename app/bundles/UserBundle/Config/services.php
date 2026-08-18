<?php

declare(strict_types=1);

use MailVotech\CoreBundle\DependencyInjection\MailVotechCoreExtension;
use MailVotech\UserBundle\EventListener\ApiUserSubscriber;
use MailVotech\UserBundle\Security\Authentication\Token\Permissions\TokenPermissions;
use MailVotech\UserBundle\Security\Authenticator\PluginAuthenticator;
use MailVotech\UserBundle\Security\Authenticator\SsoAuthenticator;
use MailVotech\UserBundle\Security\EntryPoint\MainEntryPoint;
use MailVotech\UserBundle\Security\Provider\UserProvider;

use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $excludes = [
    ];

    $services->load('MailVotech\\UserBundle\\', '../')
        ->exclude('../{'.implode(',', array_merge(MailVotechCoreExtension::DEFAULT_EXCLUDES, $excludes)).'}');

    $services->load('MailVotech\\UserBundle\\Entity\\', '../Entity/*Repository.php')
        ->tag(Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\ServiceRepositoryCompilerPass::REPOSITORY_SERVICE_TAG);

    $services->set(MailVotech\UserBundle\ApiPlatform\UserProcessor::class)
        ->args([
            service('api_platform.doctrine.orm.state.persist_processor'),
            service('security.user_password_hasher'),
        ])
        ->tag('api_platform.state_processor');

    $services->set('security.authenticator.mailvotech_sso', SsoAuthenticator::class)
        ->abstract()
        ->args([
            '$httpUtils'      => service('security.http_utils'),
            '$userProvider'   => abstract_arg('user provider'),
            '$successHandler' => abstract_arg('authentication success handler'),
            '$failureHandler' => abstract_arg('authentication failure handler'),
            '$options'        => abstract_arg('options'),
        ]);

    $services->set('security.authenticator.mailvotech_api', PluginAuthenticator::class)
        ->abstract()
        ->args([
            '$oAuth2' => service('fos_oauth_server.server'),
        ]);

    $services->set(MailVotech\UserBundle\Security\SAML\Helper::class);
    $services->set('security.token.permissions', TokenPermissions::class);

    $services->set(UserProvider::class);
    $services->alias('mailvotech.user.provider', UserProvider::class);

    $services->load('MailVotech\\UserBundle\\Security\\EntryPoint\\', '../Security/EntryPoint/*.php');
    $services->load('MailVotech\\UserBundle\\Security\\Authentication\\Token\\Permissions\\', '../Security/Authentication/Token/Permissions/*.php');

    $services->alias(MailVotech\UserBundle\Entity\UserTokenRepositoryInterface::class, MailVotech\UserBundle\Entity\UserTokenRepository::class);

    $services->alias('mailvotech.user.model.role', MailVotech\UserBundle\Model\RoleModel::class);
    $services->alias('mailvotech.user.model.user', MailVotech\UserBundle\Model\UserModel::class);
    $services->alias('mailvotech.user.repository.user_token', MailVotech\UserBundle\Entity\UserTokenRepository::class);
    $services->alias('mailvotech.user.repository', MailVotech\UserBundle\Entity\UserRepository::class);
    $services->alias('mailvotech.permission.repository', MailVotech\UserBundle\Entity\PermissionRepository::class);
    $services->alias('mailvotech.user.model.password_strength_estimator', MailVotech\UserBundle\Model\PasswordStrengthEstimatorModel::class);
    $services->get(MailVotech\UserBundle\Form\Validator\Constraints\NotWeakValidator::class)->tag('validator.constraint_validator');

    $services->load('MailVotech\\UserBundle\\Security\\SAML\Store\\Request\\', '../Security/SAML/Store/Request/*.php');
    $services->get(MailVotech\UserBundle\Security\SAML\Store\Request\RequestStateStore::class)
        ->arg('$prefix', '%lightsaml.store.request_session_prefix%')
        ->arg('$suffix', '%lightsaml.store.request_session_sufix%');
    $services->get(MainEntryPoint::class)->arg('$samlEnabled', '%env(MAILVOTECH_SAML_ENABLED)%');
    $services->get(ApiUserSubscriber::class)->arg('$userProvider', service('security.user_providers'));

    // Below are fixes for autowiring of SAML SpBundle.
    $services->alias(LightSaml\SymfonyBridgeBundle\Bridge\Container\BuildContainer::class, 'lightsaml.container.build');
    $services->load('LightSaml\\SpBundle\\Controller\\', '%kernel.project_dir%/vendor/javer/sp-bundle/src/LightSaml/SpBundle/Controller/*.php')
        ->tag('controller.service_arguments');

    $services->set(MailVotech\UserBundle\EventListener\LogoutListener::class);

    $services->set('mailvotech.security.saml.username_mapper', MailVotech\UserBundle\Security\SAML\User\UserMapper::class)
        ->arg('$attributes', ['email' => param('mailvotech.saml_idp_email_attribute'), 'username' => param('mailvotech.saml_idp_username_attribute'), 'firstname' => param('mailvotech.saml_idp_firstname_attribute'), 'lastname' => param('mailvotech.saml_idp_lastname_attribute')]);
    $services->alias(MailVotech\UserBundle\Security\SAML\User\UserMapper::class, 'mailvotech.security.saml.username_mapper');
    $services->set('mailvotech.user.manager', Doctrine\ORM\EntityManager::class)
        ->factory([service('doctrine'), 'getManagerForClass'])
        ->args([MailVotech\UserBundle\Entity\User::class]);
    $services->alias(Doctrine\ORM\EntityManager::class, 'mailvotech.user.manager');
    $services->set('mailvotech.permission.manager', Doctrine\ORM\EntityManager::class)
        ->factory([service('doctrine'), 'getManagerForClass'])
        ->args([MailVotech\UserBundle\Entity\Permission::class]);
    $services->alias(Doctrine\ORM\EntityManager::class, 'mailvotech.permission.manager');
    $services->set('mailvotech.security.saml.entity_descriptor_provider', LightSaml\Builder\EntityDescriptor\SimpleEntityDescriptorBuilder::class)
        ->factory([MailVotech\UserBundle\Security\SAML\EntityDescriptorProviderFactory::class, 'build'])
        ->args([param('lightsaml.own.entity_id'), service('router'), param('lightsaml.route.login_check'), service('lightsaml.own.credential_store')]);
    $services->alias(LightSaml\Builder\EntityDescriptor\SimpleEntityDescriptorBuilder::class, 'mailvotech.security.saml.entity_descriptor_provider');
    $services->set('mailvotech.user.fixture.role', MailVotech\UserBundle\DataFixtures\ORM\LoadRoleData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\UserBundle\DataFixtures\ORM\LoadRoleData::class, 'mailvotech.user.fixture.role');
    $services->set('mailvotech.user.fixture.user', MailVotech\UserBundle\DataFixtures\ORM\LoadUserData::class)->tag(Doctrine\Bundle\FixturesBundle\DependencyInjection\CompilerPass\FixturesCompilerPass::FIXTURE_TAG);
    $services->alias(MailVotech\UserBundle\DataFixtures\ORM\LoadUserData::class, 'mailvotech.user.fixture.user');
    $services->set('mailvotech.security.saml.credential_store', MailVotech\UserBundle\Security\SAML\Store\CredentialsStore::class)
        ->arg('$entityId', param('mailvotech.saml_idp_entity_id'))
        ->tag('lightsaml.own_credential_store');
    $services->alias(MailVotech\UserBundle\Security\SAML\Store\CredentialsStore::class, 'mailvotech.security.saml.credential_store');
    $services->set('mailvotech.security.saml.trust_store', MailVotech\UserBundle\Security\SAML\Store\TrustOptionsStore::class)
        ->arg('$entityId', param('mailvotech.saml_idp_entity_id'))
        ->tag('lightsaml.trust_options_store');
    $services->alias(MailVotech\UserBundle\Security\SAML\Store\TrustOptionsStore::class, 'mailvotech.security.saml.trust_store');
    $services->set('mailvotech.security.saml.user_creator', MailVotech\UserBundle\Security\SAML\User\UserCreator::class)
        ->arg('$defaultRole', param('mailvotech.saml_idp_default_role'));
    $services->alias(MailVotech\UserBundle\Security\SAML\User\UserCreator::class, 'mailvotech.security.saml.user_creator');

    $services->set(MailVotech\UserBundle\Security\Authentication\AuthenticationHandler::class);
    $services->alias('mailvotech.security.authentication_handler', MailVotech\UserBundle\Security\Authentication\AuthenticationHandler::class);

    $services->set('mailvotech.security.saml.entity_descriptor_store', MailVotech\UserBundle\Security\SAML\Store\EntityDescriptorStore::class)->tag('lightsaml.idp_entity_store');

    $services->set('mailvotech.security.saml.id_store', MailVotech\UserBundle\Security\SAML\Store\IdStore::class);

    $services->set(MailVotech\UserBundle\Security\UserTokenSetter::class);

    $services->set('mailvotech.user.model.user_token_service', MailVotech\UserBundle\Model\UserToken\UserTokenService::class);
    // Decorate the form_login class to ensure no user enumeration can
    // happen via timing attacks.
    $services->set('mailvotech.security.authenticator.form_login.decorator', MailVotech\UserBundle\Security\TimingSafeFormLoginAuthenticator::class)
        ->decorate('security.authenticator.form_login.main')
        ->args([
            service('.inner'),
            service('mailvotech.user.provider'),
            service('security.password_hasher_factory'),
            [], // This will be replaced by the compiler pass
        ]);
};
