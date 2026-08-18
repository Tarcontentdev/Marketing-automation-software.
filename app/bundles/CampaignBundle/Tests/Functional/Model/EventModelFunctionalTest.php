<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests\Functional\Model;

use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\CampaignBundle\Entity\Event;
use MailVotech\CampaignBundle\Model\EventModel;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;

final class EventModelFunctionalTest extends MailVotechMysqlTestCase
{
    protected $useCleanupRollback = false;

    public function testDeleteEvents(): void
    {
        // Create a campaign.
        $campaign = new Campaign();
        $campaign->setName('My campaign');
        $campaign->setIsPublished(true);
        $campaign->setDateAdded(new \DateTime());
        $this->em->persist($campaign);
        $this->em->flush();

        $currentEvents = $deletedEvents = [];
        // Create Events
        foreach (range(1, 5) as $item) {
            $event = new Event();
            $event->setName('Event '.$item);
            $event->setCampaign($campaign);
            $event->setType('sometype');
            $event->setEventType('action');
            $this->em->persist($event);
            $this->em->flush();

            if (0 === $item % 2) {
                $currentEvents[$event->getId()] = $event;
            } else {
                $deletedEvents[] = [
                    'id'                => $event->getId(),
                    'redirect_event_id' => null,
                ];
            }
        }

        // delete them
        /** @var EventModel $eventModel */
        $eventModel = self::getContainer()->get(EventModel::class);
        $eventModel->deleteEvents($currentEvents, $deletedEvents);

        $this->em->clear();

        $filter = [
            'filter' => [
                'where' => [
                    [
                        'col'  => 'e.deleted',
                        'expr' => 'isNotNull',
                    ],
                ],
            ],
        ];

        $this->assertCount(3, $eventModel->getEntities($filter));
    }
}
