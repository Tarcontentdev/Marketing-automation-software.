<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\IpLookup;

class MaxmindCountryLookup extends AbstractMaxmindLookup
{
    protected function getName(): string
    {
        return 'maxmind_country';
    }
}
