<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\IpLookup;

class MaxmindOmniLookup extends AbstractMaxmindLookup
{
    protected function getName(): string
    {
        return 'maxmind_omni';
    }
}
