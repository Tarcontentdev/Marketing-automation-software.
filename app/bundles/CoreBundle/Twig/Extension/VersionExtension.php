<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Twig\Extension;

use MailVotech\CoreBundle\Helper\AppVersion;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class VersionExtension extends AbstractExtension
{
    public function __construct(
        private readonly AppVersion $appVersion,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('mailvotechAppVersion', $this->getVersion(...)),
        ];
    }

    public function getVersion(): string
    {
        return $this->appVersion->getVersion();
    }
}
