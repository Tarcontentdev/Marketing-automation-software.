<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Test;

final class FunctionalWarmupTest extends MailVotechMysqlTestCase
{
    public function testWarmup(): void
    {
        $this->client->request('GET', '/404');
        $this->assertResponseStatusCodeSame(404);
    }
}
