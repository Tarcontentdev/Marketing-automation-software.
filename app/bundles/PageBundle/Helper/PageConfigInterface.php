<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\Helper;

interface PageConfigInterface
{
    public function isDraftEnabled(): bool;
}
