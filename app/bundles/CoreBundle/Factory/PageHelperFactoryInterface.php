<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Factory;

use MailVotech\CoreBundle\Helper\PageHelperInterface;

interface PageHelperFactoryInterface
{
    public function make(string $sessionPrefix, int $page): PageHelperInterface;
}
