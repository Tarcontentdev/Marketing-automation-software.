<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Command;

use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\CampaignBundle\Entity\Event;
use MailVotech\CampaignBundle\Entity\Lead as CampaignLead;
use MailVotech\CampaignBundle\Entity\LeadEventLog;
use MailVotech\CoreBundle\Command\RemoveAnonymousContactsCommand;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadList;
use MailVotech\LeadBundle\Entity\ListLead;

final class RemoveAnonymousContactsCommandFunctionalTest extends MailVotechMysqlTestCase
{
    /**
     * @throws \Exception
     */
    public function testDeleteAnonymousContactCommand(): void
    {
        $lead    = $this->createAnonymousLead();
        $segment = $this->createSegment();
        $this->createListLead($segment, $lead);

        $campaign = $this->createCampaign();
        $event    = $this->createEvent('Event 1', $campaign);
        $this->createCampaignLead($campaign, $lead);
        $this->createEventLog($lead, $event, $campaign);

        $this->em->flush();

        $this->assertCount(1, $this->em->getRepository(ListLead::class)->findBy(['list' => $segment]));
        $this->assertCount(1, $this->em->getRepository(CampaignLead::class)->findBy(['campaign' => $campaign]));
        $this->assertCount(1, $this->em->getRepository(LeadEventLog::class)->findBy(['campaign' => $campaign, 'lead' => $lead], ['event' => 'ASC']));

        $this->testSymfonyCommand(RemoveAnonymousContactsCommand::COMMAND_NAME);

        $this->assertCount(1, $this->em->getRepository(Lead::class)->findAll());
        $this->assertCount(0, $this->em->getRepository(ListLead::class)->findBy(['list' => $segment]));
        $this->assertCount(0, $this->em->getRepository(CampaignLead::class)->findBy(['campaign' => $campaign]));
        $this->assertCount(0, $this->em->getRepository(LeadEventLog::class)->findBy(['campaign' => $campaign, 'lead' => $lead], ['event' => 'ASC']));
    }

    private function createAnonymousLead(): Lead
    {
        $lead = new Lead();
        $this->em->persist($lead);

        return $lead;
    }

    private function createSegment(): LeadList
    {
        $segment = new LeadList();
        $segment->setName('Segment A');
        $segment->setAlias('segment-a');
        $segment->setPublicName('segment-a');
        $segment->setFilters([]);
        $this->em->persist($segment);

        return $segment;
    }

    protected function createListLead(LeadList $segment, Lead $lead): void
    {
        $segmentRef = new ListLead();
        $segmentRef->setLead($lead);
        $segmentRef->setList($segment);
        $segmentRef->setDateAdded(new \DateTime());
        $this->em->persist($segmentRef);
    }

    private function createCampaign(): Campaign
    {
        $campaign = new Campaign();
        $campaign->setName('My campaign');
        $campaign->setIsPublished(true);
        $this->em->persist($campaign);

        return $campaign;
    }

    private function createEvent(string $name, Campaign $campaign): Event
    {
        $event = new Event();
        $event->setName($name);
        $event->setCampaign($campaign);
        $event->setType('email.send');
        $event->setEventType('action');
        $event->setTriggerInterval(1);
        $event->setTriggerMode('immediate');
        $this->em->persist($event);

        return $event;
    }

    private function createEventLog(Lead $lead, Event $event, Campaign $campaign): LeadEventLog
    {
        $leadEventLog = new LeadEventLog();
        $leadEventLog->setLead($lead);
        $leadEventLog->setEvent($event);
        $leadEventLog->setCampaign($campaign);
        $this->em->persist($leadEventLog);

        return $leadEventLog;
    }

    protected function createCampaignLead(Campaign $campaign, Lead $lead): CampaignLead
    {
        $campaignLead = new CampaignLead();
        $campaignLead->setCampaign($campaign);
        $campaignLead->setLead($lead);
        $campaignLead->setDateAdded(new \DateTime());
        $this->em->persist($campaignLead);

        return $campaignLead;
    }
}
