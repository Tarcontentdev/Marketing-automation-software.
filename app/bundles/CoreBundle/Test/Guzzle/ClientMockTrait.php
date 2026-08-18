<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Test\Guzzle;

use GuzzleHttp\Handler\MockHandler;

trait ClientMockTrait
{
    private function getClientMockHandler(): MockHandler
    {
        $clientMockHandler = static::getContainer()->get(MockHandler::class);
        \assert($clientMockHandler instanceof MockHandler);

        return $clientMockHandler;
    }
}
