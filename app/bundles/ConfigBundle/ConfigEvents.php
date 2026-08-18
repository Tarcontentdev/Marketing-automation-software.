<?php

declare(strict_types=1);

namespace MailVotech\ConfigBundle;

/**
 * Events available for ConfigBundle.
 */
final class ConfigEvents
{
    /**
     * The mailvotech.config_on_generate event is thrown when the configuration form is generated.
     *
     * The event listener receives a
     * MailVotech\ConfigBundle\Event\ConfigGenerateEvent instance.
     *
     * @var string
     */
    public const CONFIG_ON_GENERATE = 'mailvotech.config_on_generate';

    /**
     * The mailvotech.config_pre_save event is thrown right before config data are saved.
     *
     * The event listener receives a MailVotech\ConfigBundle\Event\ConfigEvent instance.
     *
     * @var string
     */
    public const CONFIG_PRE_SAVE = 'mailvotech.config_pre_save';

    /**
     * The mailvotech.config_post_save event is thrown right after config data are saved.
     *
     * The event listener receives a MailVotech\ConfigBundle\Event\ConfigEvent instance.
     *
     * @var string
     */
    public const CONFIG_POST_SAVE = 'mailvotech.config_post_save';
}
