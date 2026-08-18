<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests\Controller;

use MailVotech\CampaignBundle\Entity\LeadEventLog;
use MailVotech\CampaignBundle\Entity\LeadEventLogRepository;
use MailVotech\CampaignBundle\Tests\Functional\Fixtures\FixtureHelper;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use Symfony\Component\HttpFoundation\Request;

final class AjaxControllerFunctionalTest extends MailVotechMysqlTestCase
{
    private FixtureHelper $campaignFixturesHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->campaignFixturesHelper = new FixtureHelper($this->em);
    }

    public function testCancelScheduledCampaignEventAction(): void
    {
        $this->campaignFixturesHelper = new FixtureHelper($this->em);
        $contact                      = $this->campaignFixturesHelper->createContact('some@contact.email');
        $campaign                     = $this->campaignFixturesHelper->createCampaign('Scheduled event test');
        $this->campaignFixturesHelper->addContactToCampaign($contact, $campaign);
        $this->campaignFixturesHelper->createCampaignWithScheduledEvent($campaign);
        $this->em->flush();

        $commandResult = $this->testSymfonyCommand('mailvotech:campaigns:trigger', ['--campaign-id' => $campaign->getId()]);

        $this->assertStringContainsString('1 total event was scheduled', $commandResult->getDisplay());

        $payload = [
            'action'    => 'campaign:cancelScheduledCampaignEvent',
            'eventId'   => $campaign->getEvents()[0]->getId(),
            'contactId' => $contact->getId(),
        ];

        $this->setCsrfHeader();
        $this->client->xmlHttpRequest(Request::METHOD_POST, '/s/ajax', $payload);

        // Ensure we'll fetch fresh data from the database and not from entity manager.
        $this->em->detach($contact);
        $this->em->detach($campaign);

        /** @var LeadEventLogRepository $leadEventLogRepository */
        $leadEventLogRepository = $this->em->getRepository(LeadEventLog::class);

        /** @var LeadEventLog $log */
        $log = $leadEventLogRepository->findOneBy(['lead' => $contact, 'campaign' => $campaign]);

        self::assertResponseIsSuccessful();
        $this->assertSame('{"success":1}', $this->client->getResponse()->getContent());
        $this->assertFalse($log->getIsScheduled());
    }
}
