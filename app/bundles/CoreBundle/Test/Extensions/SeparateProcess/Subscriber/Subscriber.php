<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Test\Extensions\SeparateProcess\Subscriber;

use MailVotech\CoreBundle\Test\Extensions\SeparateProcess\SeparateProcess;

abstract class Subscriber
{
    public function __construct(
        private readonly SeparateProcess $separateProcess,
    ) {
    }

    public function separateProcess(): SeparateProcess
    {
        return $this->separateProcess;
    }
}
