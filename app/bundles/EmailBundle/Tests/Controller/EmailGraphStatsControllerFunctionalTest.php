<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\Controller;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\CoreBundle\Tests\Functional\CreateTestEntitiesTrait;
use MailVotech\EmailBundle\Entity\Email;
use MailVotech\LeadBundle\Entity\LeadList;
use Symfony\Component\HttpFoundation\Request;

final class EmailGraphStatsControllerFunctionalTest extends MailVotechMysqlTestCase
{
    use CreateTestEntitiesTrait;

    public function testTemplateViewAction(): void
    {
        $email = $this->createAndPersistEmail('Email A');

        $this->client->request(Request::METHOD_GET, "/s/emails-graph-stats/{$email->getId()}/0/2022-08-21/2022-09-21");
        self::assertResponseIsSuccessful();
    }

    public function testSegmentViewAction(): void
    {
        $segment = $this->createSegment('segment-B', []);
        $email   = $this->createAndPersistEmail('Email B', $segment);

        $this->client->request(Request::METHOD_GET, "/s/emails-graph-stats/{$email->getId()}/0/2022-08-21/2022-09-21");
        self::assertResponseIsSuccessful();
    }

    private function createAndPersistEmail(string $name, ?LeadList $segment = null): Email
    {
        $email = $this->createEmail($name);
        if (null !== $segment) {
            $email->addList($segment);
        }
        $this->em->persist($email);
        $this->em->flush();

        return $email;
    }
}
