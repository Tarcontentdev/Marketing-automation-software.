<?php

namespace MailVotech\LeadBundle\Twig\Helper;

use MailVotech\CoreBundle\Twig\Helper\AssetsHelper;

final readonly class DefaultAvatarHelper
{
    public function __construct(
        private AssetsHelper $assetsHelper,
    ) {
    }

    public function getDefaultAvatar(bool $absolute = false): string
    {
        return $this->assetsHelper->getOverridableUrl('images/avatar.png', $absolute);
    }
}
