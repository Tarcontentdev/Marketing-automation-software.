<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Functional;

use MailVotech\CoreBundle\Test\AbstractMailVotechTestCase;

final class ParametersTest extends AbstractMailVotechTestCase
{
    public function testRememberMeParameterUsesIntProcessor(): void
    {
        $this->assertSame(7_776_000, self::getContainer()->getParameter('mailvotech.rememberme_lifetime'));
    }
}
