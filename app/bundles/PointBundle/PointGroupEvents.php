<?php

declare(strict_types=1);

namespace MailVotech\PointBundle;

final class PointGroupEvents
{
    /**
     * The mailvotech.group_pre_save event is thrown right before a form is persisted.
     *
     * The event listener receives a MailVotech\PointBundle\Event\ScoringCategoryEvent instance.
     *
     * @var string
     */
    public const GROUP_PRE_SAVE = 'mailvotech.group_pre_save';

    /**
     * The mailvotech.group_post_save event is thrown right after a form is persisted.
     *
     * The event listener receives a MailVotech\PointBundle\Event\ScoringCategoryEvent instance.
     *
     * @var string
     */
    public const GROUP_POST_SAVE = 'mailvotech.group_post_save';

    /**
     * The mailvotech.group_pre_delete event is thrown before a form is deleted.
     *
     * The event listener receives a MailVotech\PointBundle\Event\ScoringCategoryEvent instance.
     *
     * @var string
     */
    public const GROUP_PRE_DELETE = 'mailvotech.group_pre_delete';

    /**
     * The mailvotech.group_post_delete event is thrown after a form is deleted.
     *
     * The event listener receives a MailVotech\PointBundle\Event\ScoringCategoryEvent instance.
     *
     * @var string
     */
    public const GROUP_POST_DELETE = 'mailvotech.group_post_delete';

    /**
     * The mailvotech.group_contact_score_change event is dispatched if a group contact score changes.
     *
     * The event listener receives a MailVotech\PointBundle\Event\GroupScoreChangeEvent instance.
     *
     * @var string
     */
    public const SCORE_CHANGE = 'mailvotech.group_contact_score_change';
}
