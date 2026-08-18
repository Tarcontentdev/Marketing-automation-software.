<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Twig\Helper;

use MailVotech\CoreBundle\Helper\AppVersion;

/**
 * final class VersionHelper.
 */
final readonly class VersionHelper
{
    public function __construct(
        private AppVersion $appVersion,
    ) {
    }

    public function getName(): string
    {
        return 'version';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->appVersion->getVersion();
    }
}
