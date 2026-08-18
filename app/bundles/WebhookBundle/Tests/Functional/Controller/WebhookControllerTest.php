<?php

declare(strict_types=1);

namespace MailVotech\WebhookBundle\Tests\Controller;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\WebhookBundle\Entity\Event;
use MailVotech\WebhookBundle\Entity\Log;
use MailVotech\WebhookBundle\Entity\Webhook;
use Symfony\Component\HttpFoundation\Request;

final class WebhookControllerTest extends MailVotechMysqlTestCase
{
    public function testViewWebhookDetail(): void
    {
        $webhook = $this->createWebhook('test', 'http://domain.tld', 'secret');
        $this->createWebhookEvent($webhook, 'Type');
        for ($log = 1; $log <= 105; ++$log) {
            $this->createWebhookLog($webhook, 'test', 200);
        }
        $this->em->flush();
        $this->em->clear();
        $crawler = $this->client->request(Request::METHOD_GET, '/s/webhooks/view/'.$webhook->getId());
        self::assertResponseIsSuccessful();

        $logList = $crawler->filter('.table.table-responsive > tbody > tr')->count();
        $this->assertSame(Webhook::LOGS_DISPLAY_LIMIT, $logList);
    }

    private function createWebhook(string $name, string $url, string $secret): Webhook
    {
        $webhook = new Webhook();
        $webhook->setName($name);
        $webhook->setWebhookUrl($url);
        $webhook->setSecret($secret);
        $this->em->persist($webhook);

        return $webhook;
    }

    private function createWebhookEvent(Webhook $webhook, string $type): Event
    {
        $event = new Event();
        $event->setWebhook($webhook);
        $event->setEventType($type);
        $this->em->persist($event);

        return $event;
    }

    private function createWebhookLog(Webhook $webhook, string $note, int $statusCode): Log
    {
        $log = new Log();
        $log->setWebhook($webhook);
        $log->setNote($note);
        $log->setStatusCode($statusCode);
        $this->em->persist($log);

        return $log;
    }
}
