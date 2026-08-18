<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Test\Extensions\SlowTest\Subscriber;

use MailVotech\CoreBundle\Test\Extensions\SlowTest\SlowTest;

abstract class Subscriber
{
    public function __construct(
        private readonly SlowTest $slowTest,
    ) {
    }

    public function slowTest(): SlowTest
    {
        return $this->slowTest;
    }
}
