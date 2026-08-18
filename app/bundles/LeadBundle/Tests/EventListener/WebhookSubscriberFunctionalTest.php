<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\EventListener;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadList;
use MailVotech\LeadBundle\Entity\LeadRepository;
use MailVotech\LeadBundle\Model\ListModel;
use MailVotech\WebhookBundle\Entity\Event;
use MailVotech\WebhookBundle\Entity\Webhook;
use MailVotech\WebhookBundle\Entity\WebhookQueue;
use MailVotech\WebhookBundle\Entity\WebhookQueueRepository;
use MailVotech\WebhookBundle\Model\WebhookModel;

final class WebhookSubscriberFunctionalTest extends MailVotechMysqlTestCase
{
    protected $useCleanupRollback = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpSymfony(
            $this->configParams +
            [
                'queue_mode' => WebhookModel::COMMAND_PROCESS,
            ]
        );
        $this->truncateTables('leads', 'webhooks', 'webhook_queue', 'webhook_events');
    }

    public function testOnSegmentChange(): void
    {
        $contactRepository = $this->em->getRepository(Lead::class);
        $this->assertInstanceOf(LeadRepository::class, $contactRepository);

        /** @var ListModel $segmentModel */
        $segmentModel = self::getContainer()->get(ListModel::class);
        $this->assertInstanceOf(ListModel::class, $segmentModel);

        $webhookQueueRepository = $this->em->getRepository(WebhookQueue::class);
        $this->assertInstanceOf(WebhookQueueRepository::class, $webhookQueueRepository);

        $webhook = $this->createWebhook();

        $segment = new LeadList();
        $segment->setName('Some segment');
        $segmentModel->saveEntity($segment);

        $contacts = [new Lead()];
        $contactRepository->saveEntities($contacts);

        $this->assertFalse($webhookQueueRepository->exists($webhook->getId()));

        $segmentModel->addLead($contacts[0], $segment);

        $this->assertTrue($webhookQueueRepository->exists($webhook->getId()));

        $queueWebhook   = $webhookQueueRepository->getEntity(1);
        $decodedPayload = json_decode($queueWebhook->getPayload(), true);
        $this->assertEquals('added', $decodedPayload['action']);
    }

    private function createWebhook(): Webhook
    {
        $webhook = new Webhook();
        $event   = new Event();

        $event->setEventType('mailvotech.lead_list_change');
        $event->setWebhook($webhook);

        $webhook->addEvent($event);
        $webhook->setName('Webhook from a functional test');
        $webhook->setWebhookUrl('https:://whatever.url');
        $webhook->setSecret('any_secret_will_do');
        $webhook->isPublished(true);
        $webhook->setCreatedBy(1);

        $this->em->persist($event);
        $this->em->persist($webhook);
        $this->em->flush();

        return $webhook;
    }
}
