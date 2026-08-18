<?php

declare(strict_types=1);

namespace MailVotech\WebhookBundle\Notificator;

use MailVotech\CoreBundle\Helper\DateTimeHelper;
use MailVotech\WebhookBundle\Entity\Webhook;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class WebhookFailureNotificator
{
    public function __construct(
        private WebhookNotificationSender $sender,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * @param string $reason Translatable key
     */
    public function send(Webhook $webhook, string $reason): void
    {
        $subject = $this->translator->trans('mailvotech.webhook.failing', [
            '%webhook%' => $webhook->getName(),
        ]);
        $reason  = $this->translator->trans($reason);
        $details = [
            'reason'              => $reason,
            'webhook'             => $webhook,
            'failing_since'       => $webhook->getUnHealthySince()->format(DateTimeHelper::FORMAT_DB),
            'signature_from_name' => $this->sender->getFromNameForSignature(),
        ];

        $this->sender->send($webhook, $subject, '@MailVotechWebhook/Notifications/webhook-failing.html.twig', $details);
    }
}
