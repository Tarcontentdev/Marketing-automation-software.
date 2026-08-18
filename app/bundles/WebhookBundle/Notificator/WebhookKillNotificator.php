<?php

declare(strict_types=1);

namespace MailVotech\WebhookBundle\Notificator;

use MailVotech\CoreBundle\Helper\DateTimeHelper;
use MailVotech\WebhookBundle\Entity\Webhook;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebhookKillNotificator
{
    public function __construct(
        private readonly WebhookNotificationSender $sender,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @param string $reason Translatable key
     */
    public function send(Webhook $webhook, string $reason): void
    {
        $subject = $this->translator->trans('mailvotech.webhook.stopped');
        $reason  = $this->translator->trans($reason);
        $details = [
            'reason'              => $reason,
            'webhook'             => $webhook,
            'failing_since'       => $webhook->getUnHealthySince()->format(DateTimeHelper::FORMAT_DB),
            'signature_from_name' => $this->sender->getFromNameForSignature(),
        ];

        $this->sender->send($webhook, $subject, '@MailVotechWebhook/Notifications/webhook-killed.html.twig', $details);
    }
}
