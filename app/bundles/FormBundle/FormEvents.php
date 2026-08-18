<?php

declare(strict_types=1);

namespace MailVotech\FormBundle;

/**
 * Events available for FormBundle.
 */
final class FormEvents
{
    /**
     * The mailvotech.form_pre_save event is dispatched right before a form is persisted.
     *
     * The event listener receives a MailVotech\FormBundle\Event\FormEvent instance.
     *
     * @var string
     */
    public const FORM_PRE_SAVE = 'mailvotech.form_pre_save';

    /**
     * The mailvotech.form_post_save event is dispatched right after a form is persisted.
     *
     * The event listener receives a MailVotech\FormBundle\Event\FormEvent instance.
     *
     * @var string
     */
    public const FORM_POST_SAVE = 'mailvotech.form_post_save';

    /**
     * The mailvotech.form_pre_delete event is dispatched before a form is deleted.
     *
     * The event listener receives a MailVotech\FormBundle\Event\FormEvent instance.
     *
     * @var string
     */
    public const FORM_PRE_DELETE = 'mailvotech.form_pre_delete';

    /**
     * The mailvotech.form_post_delete event is dispatched after a form is deleted.
     *
     * The event listener receives a MailVotech\FormBundle\Event\FormEvent instance.
     *
     * @var string
     */
    public const FORM_POST_DELETE = 'mailvotech.form_post_delete';

    /**
     * The mailvotech.field_pre_save event is dispatched right before a field is persisted.
     *
     * The event listener receives a MailVotech\FormBundle\Event\FormFieldEvent instance.
     *
     * @var string
     */
    public const FIELD_PRE_SAVE = 'mailvotech.field_pre_save';

    /**
     * The mailvotech.field_post_save event is dispatched right after a field is persisted.
     *
     * The event listener receives a MailVotech\FormBundle\Event\FormFieldEvent instance.
     *
     * @var string
     */
    public const FIELD_POST_SAVE = 'mailvotech.field_post_save';

    /**
     * The mailvotech.field_pre_delete event is dispatched before a field is deleted.
     *
     * The event listener receives a MailVotech\FormBundle\Event\FormFieldEvent instance.
     *
     * @var string
     */
    public const FIELD_PRE_DELETE = 'mailvotech.field_pre_delete';

    /**
     * The mailvotech.field_post_delete event is dispatched after a field is deleted.
     *
     * The event listener receives a MailVotech\FormBundle\Event\FormFieldEvent instance.
     *
     * @var string
     */
    public const FIELD_POST_DELETE = 'mailvotech.field_post_delete';

    /**
     * The mailvotech.form_on_build event is dispatched before displaying the form builder form to allow adding of custom form
     * fields and submit actions.
     *
     * The event listener receives a MailVotech\FormBundle\Event\FormBuilderEvent instance.
     *
     * @var string
     */
    public const FORM_ON_BUILD = 'mailvotech.form_on_build';

    /**
     * The mailvotech.on_form_validate event is dispatched when a form is validated.
     *
     * The event listener receives a MailVotech\FormBundle\Event\ValidationEvent instance.
     *
     * @var string
     */
    public const ON_FORM_VALIDATE = 'mailvotech.on_form_validate';

    /**
     * The mailvotech.form_on_submit event is dispatched when a new submission is fired.
     *
     * The event listener receives a MailVotech\FormBundle\Event\SubmissionEvent instance.
     *
     * @var string
     */
    public const FORM_ON_SUBMIT = 'mailvotech.form_on_submit';

    /**
     * The mailvotech.form.on_campaign_trigger_condition event is fired when the campaign condition triggers.
     *
     * The event listener receives a
     * MailVotech\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_TRIGGER_CONDITION = 'mailvotech.form.on_campaign_trigger_condition';

    /**
     * The mailvotech.form.on_campaign_trigger_decision event is fired when the campaign decision triggers.
     *
     * The event listener receives a
     * MailVotech\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_TRIGGER_DECISION = 'mailvotech.form.on_campaign_trigger_decision';

    /**
     * The mailvotech.form.on_execute_submit_action event is dispatched to excecute the form submit actions.
     *
     * The event listener receives a
     * MailVotech\FormBundle\Event\SubmissionEvent
     *
     * @var string
     */
    public const ON_EXECUTE_SUBMIT_ACTION = 'mailvotech.form.on_execute_submit_action';

    /**
     * The mailvotech.form.on_submission_rate_winner event is fired when there is a need to determine submission rate winner.
     *
     * The event listener receives a
     * MailVotech\CoreBundles\Event\DetermineWinnerEvent
     *
     * @var string
     */
    public const ON_DETERMINE_SUBMISSION_RATE_WINNER = 'mailvotech.form.on_submission_rate_winner';

    /**
     * The mailvotech.form.on_object_collect event is fired when there is a call for all available objects that can provide fields for mapping.
     *
     * The event listener receives a
     * MailVotech\CoreBundles\Event\ObjectCollectEvent
     *
     * @var string
     */
    public const ON_OBJECT_COLLECT = 'mailvotech.form.on_object_collect';

    /**
     * The mailvotech.form.on_field_collect event is fired when there is a call for all available fields for specific object that can be provided for mapping.
     *
     * The event listener receives a
     * MailVotech\CoreBundles\Event\FieldCollectEvent
     *
     * @var string
     */
    public const ON_FIELD_COLLECT = 'mailvotech.form.on_field_collect';
}
