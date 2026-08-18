<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Twig\Extension;

use MailVotech\LeadBundle\Exception\UnknownDncReasonException;
use MailVotech\LeadBundle\Twig\Helper\DncReasonHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class DncReasonExtension extends AbstractExtension
{
    public function __construct(
        private readonly DncReasonHelper $helper,
    ) {
    }

    /**
     * @see Twig_Extension::getFunctions()
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('dncReasonToText', $this->toText(...)),
        ];
    }

    /**
     * Convert DNC reason ID to text.
     *
     * @throws UnknownDncReasonException
     */
    public function toText(int $reasonId): string
    {
        return $this->helper->toText($reasonId);
    }
}
