<?php

declare(strict_types=1);

namespace MailVotech\DynamicContentBundle;

/**
 * Events available for DynamicContentBundle.
 */
final class DynamicContentEvents
{
    /**
     * The mailvotech.dwc_token_replacement event is thrown right before the content is returned.
     *
     * The event listener receives a
     * MailVotech\CoreBundle\Event\TokenReplacementEvent instance.
     *
     * @var string
     */
    public const TOKEN_REPLACEMENT = 'mailvotech.dwc_token_replacement';

    /**
     * The mailvotech.dwc_pre_save event is thrown right before a asset is persisted.
     *
     * The event listener receives a
     * MailVotech\DynamicContentBundle\Event\DynamicContentEvent instance.
     *
     * @var string
     */
    public const PRE_SAVE = 'mailvotech.dwc_pre_save';

    /**
     * The mailvotech.dwc_post_save event is thrown right after a asset is persisted.
     *
     * The event listener receives a
     * MailVotech\DynamicContentBundle\Event\DynamicContentEvent instance.
     *
     * @var string
     */
    public const POST_SAVE = 'mailvotech.dwc_post_save';

    /**
     * The mailvotech.dwc_pre_delete event is thrown prior to when a asset is deleted.
     *
     * The event listener receives a
     * MailVotech\DynamicContentBundle\Event\DynamicContentEvent instance.
     *
     * @var string
     */
    public const PRE_DELETE = 'mailvotech.dwc_pre_delete';

    /**
     * The mailvotech.dwc_post_delete event is thrown after a asset is deleted.
     *
     * The event listener receives a
     * MailVotech\DynamicContentBundle\Event\DynamicContentEvent instance.
     *
     * @var string
     */
    public const POST_DELETE = 'mailvotech.dwc_post_delete';

    /**
     * The mailvotech.category_pre_save event is thrown right before a category is persisted.
     *
     * The event listener receives a
     * MailVotech\CategoryBundle\Event\CategoryEvent instance.
     *
     * @var string
     */
    public const CATEGORY_PRE_SAVE = 'mailvotech.category_pre_save';

    /**
     * The mailvotech.category_post_save event is thrown right after a category is persisted.
     *
     * The event listener receives a
     * MailVotech\CategoryBundle\Event\CategoryEvent instance.
     *
     * @var string
     */
    public const CATEGORY_POST_SAVE = 'mailvotech.category_post_save';

    /**
     * The mailvotech.category_pre_delete event is thrown prior to when a category is deleted.
     *
     * The event listener receives a
     * MailVotech\CategoryBundle\Event\CategoryEvent instance.
     *
     * @var string
     */
    public const CATEGORY_PRE_DELETE = 'mailvotech.category_pre_delete';

    /**
     * The mailvotech.category_post_delete event is thrown after a category is deleted.
     *
     * The event listener receives a
     * MailVotech\CategoryBundle\Event\CategoryEvent instance.
     *
     * @var string
     */
    public const CATEGORY_POST_DELETE = 'mailvotech.category_post_delete';

    /**
     * The mailvotech.asset.on_campaign_trigger_decision event is fired when the campaign decision triggers.
     *
     * The event listener receives a
     * MailVotech\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_TRIGGER_DECISION = 'mailvotech.dwc.on_campaign_trigger_decision';

    /**
     * The mailvotech.asset.on_campaign_trigger_action event is fired when the campaign action triggers.
     *
     * The event listener receives a
     * MailVotech\CampaignBundle\Event\CampaignExecutionEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_TRIGGER_ACTION = 'mailvotech.dwc.on_campaign_trigger_action';

    /**
     * The mailvotech.dwc.on_contact_filters_evaluate event is fired when dynamic content's decision's
     * filters need to be evaluated.
     *
     * The event listener receives a
     * MailVotech\DynamicContentBundle\Event\ContactFiltersEvaluateEvent
     *
     * @var string
     */
    public const ON_CONTACTS_FILTER_EVALUATE = 'mailvotech.dwc.on_contact_filters_evaluate';
}
