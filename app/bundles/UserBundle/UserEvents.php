<?php

declare(strict_types=1);

namespace MailVotech\UserBundle;

/**
 * Events available for UserBundle.
 */
final class UserEvents
{
    /**
     * The mailvotech.user_pre_save event is dispatched right before a user is persisted.
     *
     * The event listener receives a MailVotech\UserBundle\Event\UserEvent instance.
     *
     * @var string
     */
    public const USER_PRE_SAVE = 'mailvotech.user_pre_save';

    /**
     * The mailvotech.user_post_save event is dispatched right after a user is persisted.
     *
     * The event listener receives a MailVotech\UserBundle\Event\UserEvent instance.
     *
     * @var string
     */
    public const USER_POST_SAVE = 'mailvotech.user_post_save';

    /**
     * The mailvotech.user_pre_delete event is dispatched prior to when a user is deleted.
     *
     * The event listener receives a MailVotech\UserBundle\Event\UserEvent instance.
     *
     * @var string
     */
    public const USER_PRE_DELETE = 'mailvotech.user_pre_delete';

    /**
     * The mailvotech.user_post_delete event is dispatched after a user is deleted.
     *
     * The event listener receives a MailVotech\UserBundle\Event\UserEvent instance.
     *
     * @var string
     */
    public const USER_POST_DELETE = 'mailvotech.user_post_delete';

    /**
     * The mailvotech.role_pre_save event is dispatched right before a role is persisted.
     *
     * The event listener receives a MailVotech\UserBundle\Event\RoleEvent instance.
     *
     * @var string
     */
    public const ROLE_PRE_SAVE = 'mailvotech.role_pre_save';

    /**
     * The mailvotech.role_post_save event is dispatched right after a role is persisted.
     *
     * The event listener receives a MailVotech\UserBundle\Event\RoleEvent instance.
     *
     * @var string
     */
    public const ROLE_POST_SAVE = 'mailvotech.role_post_save';

    /**
     * The mailvotech.role_pre_delete event is dispatched prior a role being deleted.
     *
     * The event listener receives a MailVotech\UserBundle\Event\RoleEvent instance.
     *
     * @var string
     */
    public const ROLE_PRE_DELETE = 'mailvotech.role_pre_delete';

    /**
     * The mailvotech.role_post_delete event is dispatched after a role is deleted.
     *
     * The event listener receives a MailVotech\UserBundle\Event\RoleEvent instance.
     *
     * @var string
     */
    public const ROLE_POST_DELETE = 'mailvotech.role_post_delete';

    /**
     * The mailvotech.user_logout event is dispatched during the logout routine giving a chance to carry out tasks before
     * the session is lost.
     *
     * The event listener receives a MailVotech\UserBundle\Event\LogoutEvent instance.
     *
     * @var string
     */
    public const USER_LOGOUT = 'mailvotech.user_logout';

    /**
     * The mailvotech.user_login event is dispatched right after a user logs in.
     *
     * The event listener receives a MailVotech\UserBundle\Event\LoginEvent instance.
     *
     * @var string
     */
    public const USER_LOGIN = 'mailvotech.user_login';

    /**
     * The mailvotech.user_form_authentication event is dispatched when a user logs in so that listeners can authenticate a user, i.e. via a 3rd party service.
     *
     * The event listener receives a MailVotech\UserBundle\Event\AuthenticationEvent instance.
     *
     * @var string
     */
    public const USER_FORM_AUTHENTICATION = 'mailvotech.user_form_authentication';

    /**
     * The mailvotech.user_pre_authentication event is dispatched when a user browses a page under /s/ except for /login. This allows support for
     * 3rd party authentication providers outside the login form.
     *
     * The event listener receives a MailVotech\UserBundle\Event\AuthenticationEvent instance.
     *
     * @var string
     */
    public const USER_PRE_AUTHENTICATION = 'mailvotech.user_pre_authentication';

    /**
     * The mailvotech.user_authentication_content event is dispatched to collect HTML from plugins to be injected into the UI to assist with
     * authentication.
     *
     * The event listener receives a MailVotech\UserBundle\Event\AuthenticationContentEvent instance.
     *
     * @var string
     */
    public const USER_AUTHENTICATION_CONTENT = 'mailvotech.user_authentication_content';

    /**
     * The mailvotech.user_form_post_local_password_authentication event is dispatched after mailvotech checks if user's local password is correct
     * This can be used to validate passwords, usernames, etc.
     *
     * The event listener receives a MailVotech\UserBundle\Event\AuthenticationContentEvent instance.
     *
     * @var string
     */
    public const USER_FORM_POST_LOCAL_PASSWORD_AUTHENTICATION ='mailvotech.user_form_post_local_password_authentication';

    /**
     * The mailvotech.user_password_strength_validation event is dispatched after mailvotech checks if user's password meets the strength requirements
     * This can be used to add custom password requirements.
     *
     * The event listener receives a MailVotech\UserBundle\Event\PasswordStrengthValidateEvent instance.
     *
     * @var string
     */
    public const USER_PASSWORD_STRENGTH_VALIDATION = 'mailvotech.user_password_strength_validation';
}
