<?php

declare(strict_types=1);

namespace MailVotech\NotificationBundle\Tests\Functional\Controller;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class PopupControllerTest extends MailVotechMysqlTestCase
{
    /**
     * Smoke test to ensure the '/s/notifications' route loads.
     */
    public function testIndexRouteSuccessfullyLoads(): void
    {
        $this->client->request(Request::METHOD_GET, '/notification');
        $response = $this->client->getResponse();

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }
}
