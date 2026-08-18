<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Helper;

interface EmailConfigInterface
{
    public function isDraftEnabled(): bool;
}
