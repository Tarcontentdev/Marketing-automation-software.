<?php

declare(strict_types=1);

namespace MailVotech\WebhookBundle\Tests\Functional\Command;

use GuzzleHttp\Psr7\Response;
use MailVotech\CoreBundle\Test\Guzzle\ClientMockTrait;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\WebhookBundle\Command\ProcessWebhookQueuesCommand;
use MailVotech\WebhookBundle\Entity\Event;
use MailVotech\WebhookBundle\Entity\Log;
use MailVotech\WebhookBundle\Entity\Webhook;
use MailVotech\WebhookBundle\Entity\WebhookQueue;
use MailVotech\WebhookBundle\Model\WebhookModel;
use Psr\Http\Message\RequestInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ProcessWebhookQueuesCommandTest extends MailVotechMysqlTestCase
{
    use ClientMockTrait;

    protected function setUp(): void
    {
        $this->configParams['queue_mode']    = WebhookModel::COMMAND_PROCESS;
        $this->configParams['webhook_limit'] = 3;

        parent::setUp();
    }

    public function testCommand(): void
    {
        $webhook      = $this->createWebhook('test', 'https://httpbin.org/post', 'secret');
        $event        = $this->createWebhookEvent($webhook, 'Type');
        $handlerStack = $this->getClientMockHandler();
        $queueIds     = [];

        // Generate 10 queue records.
        for ($i = 1; $i <= 10; ++$i) {
            $addedLog = $this->createWebhookQueue($webhook, $event, "Some payload {$i}");
            $queueIds[] = $addedLog->getId();

            $handlerStack->append(
                function (RequestInterface $request): Response {
                    $this->assertSame('POST', $request->getMethod());
                    $this->assertSame('https://httpbin.org/post', $request->getUri()->__toString());

                    return new Response(SymfonyResponse::HTTP_OK);
                }
            );
        }

        // Process queue records from 4 to 9 including. 6 in total.
        $output = $this->testSymfonyCommand(
            ProcessWebhookQueuesCommand::COMMAND_NAME,
            ['--webhook-id' => $webhook->getId(), '--min-id' => $queueIds[3], '--max-id' => $queueIds[8]]
        );
        $this->assertStringContainsString('Webhook Processing Complete', $output->getDisplay());

        // There will be 2 batches of webhook events sent. We've set we want to send 3 events per batch.
        $this->assertCount(2, $this->em->getRepository(Log::class)->findBy(['webhook' => $webhook]));

        // And 4 out of 10 queue records will be left alone as they did not fit the ID range.
        $this->assertCount(4, $this->em->getRepository(WebhookQueue::class)->findBy(['webhook' => $webhook]));
    }

    public function testCommandWhenNoWebhooksFound(): void
    {
        $output = $this->testSymfonyCommand(ProcessWebhookQueuesCommand::COMMAND_NAME);

        $this->assertStringContainsString('There are no published webhooks to process.', $output->getDisplay());
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

    private function createWebhookQueue(Webhook $webhook, Event $event, string $payload): WebhookQueue
    {
        $record = new WebhookQueue();
        $record->setWebhook($webhook);
        $record->setEvent($event);
        $record->setPayload($payload);
        $record->setDateAdded(new \DateTime());
        $this->em->persist($record);
        $this->em->flush();

        return $record;
    }
}
