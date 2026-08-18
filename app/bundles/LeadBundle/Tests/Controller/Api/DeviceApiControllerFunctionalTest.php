<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Controller\Api;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\LeadBundle\Entity\Lead;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class DeviceApiControllerFunctionalTest extends MailVotechMysqlTestCase
{
    public function testPutEditWithInexistingIdSoItShouldCreate(): void
    {
        $contact = new Lead();
        $this->em->persist($contact);
        $this->em->flush();

        $this->client->request(Request::METHOD_PUT, '/api/devices/99999/edit', [
            'device'            => 'desktop',
            'deviceOsName'      => 'Ubuntu',
            'deviceOsShortName' => 'UBT',
            'deviceOsPlatform'  => 'x64',
            'lead'              => $contact->getId(),
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }
}
