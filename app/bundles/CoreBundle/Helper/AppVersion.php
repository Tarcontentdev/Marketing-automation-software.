<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Helper;

class AppVersion
{
    /**
     * @return string
     */
    public function getVersion()
    {
        return MAILVOTECH_VERSION;
    }
}
