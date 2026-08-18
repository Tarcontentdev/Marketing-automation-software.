<?php

declare(strict_types=1);

namespace MailVotech\ChannelBundle;

final class ChannelEvents
{
    /**
     * The mailvotech.add_channel event registers communication channels.
     *
     * The event listener receives a MailVotech\ChannelBundle\Event\ChannelEvent instance.
     *
     * @var string
     */
    public const ADD_CHANNEL = 'mailvotech.add_channel';

    /**
     * The mailvotech.channel_broadcast event is dispatched by the mailvotech:send:broadcast command to process communication to pending contacts.
     *
     * The event listener receives a MailVotech\ChannelBundle\Event\ChannelBroadcastEvent instance.
     *
     * @var string
     */
    public const CHANNEL_BROADCAST = 'mailvotech.channel_broadcast';

    /**
     * The mailvotech.message_queued event is dispatched to save a message to the queue.
     *
     * The event listener receives a MailVotech\ChannelBundle\Event\MessageQueueEvent instance.
     *
     * @var string
     */
    public const MESSAGE_QUEUED = 'mailvotech.message_queued';

    /**
     * The mailvotech.process_message_queue event is dispatched to be processed by a listener.
     *
     * The event listener receives a MailVotech\ChannelBundle\Event\MessageQueueProcessEvent instance.
     *
     * @var string
     */
    public const PROCESS_MESSAGE_QUEUE = 'mailvotech.process_message_queue';

    /**
     * The mailvotech.process_message_queue_batch event is dispatched to process a batch of messages by channel and channel ID.
     *
     * The event listener receives a MailVotech\ChannelBundle\Event\MessageQueueBatchProcessEvent instance.
     *
     * @var string
     */
    public const PROCESS_MESSAGE_QUEUE_BATCH = 'mailvotech.process_message_queue_batch';

    /**
     * The mailvotech.channel.on_campaign_batch_action event is dispatched when the campaign action triggers.
     *
     * The event listener receives a MailVotech\CampaignBundle\Event\PendingEvent
     *
     * @var string
     */
    public const ON_CAMPAIGN_BATCH_ACTION = 'mailvotech.channel.on_campaign_batch_action';

    /**
     * The mailvotech.message_pre_save event is dispatched right before a form is persisted.
     *
     * The event listener receives a
     * MailVotech\ChannelEvent\Event\MessageEvent instance.
     *
     * @var string
     */
    public const MESSAGE_PRE_SAVE = 'mailvotech.message_pre_save';

    /**
     * The mailvotech.message_post_save event is dispatched right after a form is persisted.
     *
     * The event listener receives a
     * MailVotech\ChannelEvent\Event\MessageEvent instance.
     *
     * @var string
     */
    public const MESSAGE_POST_SAVE = 'mailvotech.message_post_save';

    /**
     * The mailvotech.message_pre_delete event is dispatched before a form is deleted.
     *
     * The event listener receives a
     * MailVotech\ChannelEvent\Event\MessageEvent instance.
     *
     * @var string
     */
    public const MESSAGE_PRE_DELETE = 'mailvotech.message_pre_delete';

    /**
     * The mailvotech.message_post_delete event is dispatched after a form is deleted.
     *
     * The event listener receives a
     * MailVotech\ChannelEvent\Event\MessageEvent instance.
     *
     * @var string
     */
    public const MESSAGE_POST_DELETE = 'mailvotech.message_post_delete';
}
