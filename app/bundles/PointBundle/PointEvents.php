<?php

declare(strict_types=1);

namespace MailVotech\PointBundle;

/**
 * Events available for PointBundle.
 */
final class PointEvents
{
    /**
     * The mailvotech.point_pre_save event is thrown right before a form is persisted.
     *
     * The event listener receives a MailVotech\PointBundle\Event\PointEvent instance.
     *
     * @var string
     */
    public const POINT_PRE_SAVE = 'mailvotech.point_pre_save';

    /**
     * The mailvotech.point_post_save event is thrown right after a form is persisted.
     *
     * The event listener receives a MailVotech\PointBundle\Event\PointEvent instance.
     *
     * @var string
     */
    public const POINT_POST_SAVE = 'mailvotech.point_post_save';

    /**
     * The mailvotech.point_pre_delete event is thrown before a form is deleted.
     *
     * The event listener receives a MailVotech\PointBundle\Event\PointEvent instance.
     *
     * @var string
     */
    public const POINT_PRE_DELETE = 'mailvotech.point_pre_delete';

    /**
     * The mailvotech.point_post_delete event is thrown after a form is deleted.
     *
     * The event listener receives a MailVotech\PointBundle\Event\PointEvent instance.
     *
     * @var string
     */
    public const POINT_POST_DELETE = 'mailvotech.point_post_delete';

    /**
     * The mailvotech.point_on_build event is thrown before displaying the point builder form to allow adding of custom actions.
     *
     * The event listener receives a MailVotech\PointBundle\Event\PointBuilderEvent instance.
     *
     * @var string
     */
    public const POINT_ON_BUILD = 'mailvotech.point_on_build';

    /**
     * The mailvotech.point_on_action event is thrown to execute a point action.
     *
     * The event listener receives a MailVotech\PointBundle\Event\PointActionEvent instance.
     *
     * @var string
     */
    public const POINT_ON_ACTION = 'mailvotech.point_on_action';

    /**
     * The mailvotech.point_pre_save event is thrown right before a form is persisted.
     *
     * The event listener receives a MailVotech\PointBundle\Event\TriggerEvent instance.
     *
     * @var string
     */
    public const TRIGGER_PRE_SAVE = 'mailvotech.trigger_pre_save';

    /**
     * The mailvotech.trigger_post_save event is thrown right after a form is persisted.
     *
     * The event listener receives a MailVotech\PointBundle\Event\TriggerEvent instance.
     *
     * @var string
     */
    public const TRIGGER_POST_SAVE = 'mailvotech.trigger_post_save';

    /**
     * The mailvotech.trigger_pre_delete event is thrown before a form is deleted.
     *
     * The event listener receives a MailVotech\PointBundle\Event\TriggerEvent instance.
     *
     * @var string
     */
    public const TRIGGER_PRE_DELETE = 'mailvotech.trigger_pre_delete';

    /**
     * The mailvotech.trigger_post_delete event is thrown after a form is deleted.
     *
     * The event listener receives a MailVotech\PointBundle\Event\TriggerEvent instance.
     *
     * @var string
     */
    public const TRIGGER_POST_DELETE = 'mailvotech.trigger_post_delete';

    /**
     * The mailvotech.trigger_on_build event is thrown before displaying the trigger builder form to allow adding of custom actions.
     *
     * The event listener receives a MailVotech\PointBundle\Event\TriggerBuilderEvent instance.
     *
     * @var string
     */
    public const TRIGGER_ON_BUILD = 'mailvotech.trigger_on_build';

    /**
     * The mailvotech.trigger_on_event_execute event is thrown to execute a trigger event.
     *
     * The event listener receives a MailVotech\PointBundle\Event\TriggerExecutedEvent instance.
     *
     * @var string
     */
    public const TRIGGER_ON_EVENT_EXECUTE = 'mailvotech.trigger_on_event_execute';

    /**
     * The mailvotech.trigger_on_lead_segments_change event is thrown to change lead's segments.
     *
     * The event listener receives a MailVotech\PointBundle\Event\TriggerExecutedEvent instance.
     *
     * @var string
     */
    public const TRIGGER_ON_LEAD_SEGMENTS_CHANGE = 'mailvotech.trigger_on_lead_segments_change';
}
