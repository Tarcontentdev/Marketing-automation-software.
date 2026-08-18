<?php

declare(strict_types=1);

namespace MailVotech\WebhookBundle\Tests\Entity;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\WebhookBundle\Entity\Event;
use MailVotech\WebhookBundle\Entity\Webhook;
use MailVotech\WebhookBundle\Entity\WebhookQueue;

final class WebhookQueueFunctionalTest extends MailVotechMysqlTestCase
{
    public function testPayloadCompressed(): void
    {
        $webhookQueue = $this->createWebhookQueue();

        $payload  = 'Compressed payload';
        $webhookQueue->setPayload($payload);

        $this->assertSame($payload, $webhookQueue->getPayload());

        $this->em->flush();

        $payloadDbValues = $this->fetchPayloadDbValues($webhookQueue);
        $this->assertSame($payload, gzuncompress($payloadDbValues['payload_compressed']));

        $this->em->clear();
        $webhookQueue = $this->em->getRepository(WebhookQueue::class)
            ->find($webhookQueue->getId());
        $this->assertInstanceOf(WebhookQueue::class, $webhookQueue);

        $this->assertSame($payload, $webhookQueue->getPayload());
    }

    private function createWebhookQueue(): WebhookQueue
    {
        $webhook = new Webhook();
        $webhook->setName('Test');
        $webhook->setWebhookUrl('http://domain.tld');
        $webhook->setSecret('secret');
        $this->em->persist($webhook);

        $even = new Event();
        $even->setWebhook($webhook);
        $even->setEventType('Type');
        $this->em->persist($even);

        $webhookQueue = new WebhookQueue();
        $webhookQueue->setWebhook($webhook);
        $webhookQueue->setEvent($even);
        $this->em->persist($webhookQueue);

        return $webhookQueue;
    }

    /**
     * @return mixed[]
     */
    private function fetchPayloadDbValues(WebhookQueue $webhookQueue): array
    {
        $prefix = self::getContainer()->getParameter('mailvotech.db_table_prefix');
        $query  = sprintf('SELECT payload_compressed FROM %swebhook_queue WHERE id = ?', $prefix);

        return $this->connection->executeQuery($query, [$webhookQueue->getId()])
            ->fetchAssociative();
    }
}
