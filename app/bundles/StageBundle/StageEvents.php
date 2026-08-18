<?php

declare(strict_types=1);

namespace MailVotech\StageBundle;

/**
 * Events available for StageBundle.
 */
final class StageEvents
{
    /**
     * The mailvotech.stage_pre_save event is thrown right before a form is persisted.
     *
     * The event listener receives a MailVotech\StageBundle\Event\StageEvent instance.
     *
     * @var string
     */
    public const STAGE_PRE_SAVE = 'mailvotech.stage_pre_save';

    /**
     * The mailvotech.stage_post_save event is thrown right after a form is persisted.
     *
     * The event listener receives a MailVotech\StageBundle\Event\StageEvent instance.
     *
     * @var string
     */
    public const STAGE_POST_SAVE = 'mailvotech.stage_post_save';

    /**
     * The mailvotech.stage_pre_delete event is thrown before a form is deleted.
     *
     * The event listener receives a MailVotech\StageBundle\Event\StageEvent instance.
     *
     * @var string
     */
    public const STAGE_PRE_DELETE = 'mailvotech.stage_pre_delete';

    /**
     * The mailvotech.stage_post_delete event is thrown after a form is deleted.
     *
     * The event listener receives a MailVotech\StageBundle\Event\StageEvent instance.
     *
     * @var string
     */
    public const STAGE_POST_DELETE = 'mailvotech.stage_post_delete';

    /**
     * The mailvotech.stage_on_build event is thrown before displaying the stage builder form to allow adding of custom actions.
     *
     * The event listener receives a MailVotech\StageBundle\Event\StageBuilderEvent instance.
     *
     * @var string
     */
    public const STAGE_ON_BUILD = 'mailvotech.stage_on_build';

    /**
     * The mailvotech.stage_on_action event is thrown to execute a stage action.
     *
     * The event listener receives a MailVotech\StageBundle\Event\StageActionEvent instance.
     *
     * @var string
     */
    public const STAGE_ON_ACTION = 'mailvotech.stage_on_action';

    /**
     * The mailvotech.stage.on_campaign_batch_action event is dispatched when the campaign action triggers.
     *
     * The event listener receives a MailVotech\CampaignBundle\Event\PendingEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_BATCH_ACTION = 'mailvotech.stage.on_campaign_batch_action';

    /**
     * @deprecated; use ON_CAMPAIGN_BATCH_ACTION instead
     *
     * The mailvotech.stage.on_campaign_trigger_action event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * MailVotech\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_TRIGGER_ACTION = 'mailvotech.stage.on_campaign_trigger_action';
}
