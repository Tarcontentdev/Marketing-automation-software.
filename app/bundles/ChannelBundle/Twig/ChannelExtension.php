<?php

declare(strict_types=1);

namespace MailVotech\ChannelBundle\Twig;

use MailVotech\ChannelBundle\Helper\ChannelListHelper;
use MailVotech\LeadBundle\Exception\UnknownDncReasonException;
use MailVotech\LeadBundle\Twig\Helper\DncReasonHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ChannelExtension extends AbstractExtension
{
    public function __construct(
        private readonly DncReasonHelper $dncReasonHelper,
        private readonly ChannelListHelper $channelListHelper,
    ) {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getChannelDncText', $this->getChannelDncText(...)),
            new TwigFunction('getChannelLabel', $this->getChannelLabel(...)),
        ];
    }

    public function getChannelDncText(int $reasonId): string
    {
        try {
            return $this->dncReasonHelper->toText($reasonId);
        } catch (UnknownDncReasonException $e) {
            return $e->getMessage();
        }
    }

    public function getChannelLabel(string $channel): string
    {
        return $this->channelListHelper->getChannelLabel($channel);
    }
}
