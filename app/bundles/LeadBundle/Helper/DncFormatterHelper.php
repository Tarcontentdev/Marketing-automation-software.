<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Helper;

use MailVotech\ChannelBundle\Helper\ChannelListHelper;
use MailVotech\LeadBundle\Entity\DoNotContact;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DncFormatterHelper
{
    /**
     * @var array<int, string>|null
     */
    private ?array $dncReasons = null;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly ChannelListHelper $channelListHelper,
    ) {
    }

    /**
     * Returns all available DNC reasons.
     *
     * @return array<int, string>
     */
    public function getDncReasons(): array
    {
        if (null === $this->dncReasons) {
            $this->dncReasons = [
                DoNotContact::IS_CONTACTABLE => $this->translator->trans('mailvotech.lead.report.dnc_contactable'),
                DoNotContact::UNSUBSCRIBED   => $this->translator->trans('mailvotech.lead.report.dnc_unsubscribed'),
                DoNotContact::BOUNCED        => $this->translator->trans('mailvotech.lead.report.dnc_bounced'),
                DoNotContact::MANUAL         => $this->translator->trans('mailvotech.lead.report.dnc_manual'),
            ];
        }

        return $this->dncReasons;
    }

    /**
     * Gets the label for a specific DNC reason.
     *
     * @throws \InvalidArgumentException if the reason ID is invalid
     */
    public function getDncReasonLabel(int $reasonId): string
    {
        $reasons = $this->getDncReasons();

        if (!isset($reasons[$reasonId])) {
            throw new \InvalidArgumentException(sprintf('Invalid DNC reason ID: %d', $reasonId));
        }

        return $reasons[$reasonId];
    }

    public function printReasonWithChannel(int $reason, string $channel): string
    {
        return $this->getDncReasonLabel($reason).': '.$this->channelListHelper->getChannelLabel($channel);
    }
}
