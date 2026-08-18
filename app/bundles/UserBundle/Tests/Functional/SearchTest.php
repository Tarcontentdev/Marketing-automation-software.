<?php

declare(strict_types=1);

namespace MailVotech\UserBundle\Tests\Functional;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use Symfony\Component\HttpFoundation\Response;

final class SearchTest extends MailVotechMysqlTestCase
{
    public function testSearchingUsersByName(): void
    {
        $this->client->request('GET', 's/users?search=name:admin');

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
        $this->assertStringContainsString('admin', (string) $this->client->getResponse()->getContent());
    }
}
