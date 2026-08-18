<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\EventListener;

use MailVotech\EmailBundle\EmailEvents;
use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Event\EmailSendEvent;
use MailVotech\EmailBundle\EventListener\WebhookSubscriber;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\WebhookBundle\Event\WebhookBuilderEvent;
use MailVotech\WebhookBundle\Model\WebhookModel;
use PHPUnit\Framework\MockObject\MockObject;

final class WebhookSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject&WebhookModel
     */
    private MockObject $webhookModel;

    private WebhookSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webhookModel = $this->createMock(WebhookModel::class);
        $this->subscriber   = new WebhookSubscriber($this->webhookModel, true);
    }

    public function testOnWebhookBuild(): void
    {
        $event   = $this->createMock(WebhookBuilderEvent::class);
        $matcher = $this->exactly(2);

        $event->expects($matcher)
            ->method('addEvent')->willReturnCallback(function (...$parameters) use ($matcher): void {
                if (1 === $matcher->numberOfInvocations()) {
                    $this->assertSame(EmailEvents::EMAIL_ON_SEND, $parameters[0]);
                    $this->assertSame([
                        'label'       => 'mailvotech.email.webhook.event.send',
                        'description' => 'mailvotech.email.webhook.event.send_desc',
                    ], $parameters[1]);
                }
                if (2 === $matcher->numberOfInvocations()) {
                    $this->assertSame(EmailEvents::EMAIL_ON_OPEN, $parameters[0]);
                    $this->assertSame([
                        'label'       => 'mailvotech.email.webhook.event.open',
                        'description' => 'mailvotech.email.webhook.event.open_desc',
                    ], $parameters[1]);
                }
            });

        $this->subscriber->onWebhookBuild($event);
    }

    public function testOnEmailSendWhenInternalSend(): void
    {
        $event = $this->createMock(EmailSendEvent::class);

        $event->expects($this->once())
            ->method('isInternalSend')
            ->willReturn(true);

        $this->webhookModel->expects($this->never())
            ->method('queueWebhooksByType');

        $this->subscriber->onEmailSend($event);
    }

    public function testOnEmailSend(): void
    {
        $event   = $this->createMock(EmailSendEvent::class);
        $contact = $this->createStub(Lead::class);
        $email   = $this->createStub(Email::class);
        $tokens  = ['{unsubscribe_text}' => '<a href=\"https://...'];
        $headers = ['List-Unsubscribe' => '<a href=\"https://...'];
        $source  = ['List-Unsubscribe' => '<a href=\"https://...']; // todo find out real source example

        $event->expects($this->once())
            ->method('isInternalSend')
            ->willReturn(false);

        $event->expects($this->once())
            ->method('getEmail')
            ->willReturn($email);

        $event
            ->method('getLead')
            ->willReturn($contact);

        $event->expects($this->once())
            ->method('getTokens')
            ->willReturn($tokens);

        $event->expects($this->once())
            ->method('getContentHash')
            ->willReturn('8bb9181fa1c76671352b79565e244240');

        $event->expects($this->once())
            ->method('getIdHash')
            ->willReturn('5cdaa50e155f9732391159');

        $event->expects($this->once())
            ->method('getContent')
            ->willReturn('<!DOCTYPE html><html>...');

        $event->expects($this->once())
            ->method('getSubject')
            ->willReturn('Test Email');

        $event->expects($this->once())
            ->method('getSource')
            ->willReturn($source);

        $event->expects($this->once())
            ->method('getTextHeaders')
            ->willReturn($headers);

        $this->webhookModel->expects($this->once())
            ->method('queueWebhooksByType')
            ->with(
                EmailEvents::EMAIL_ON_SEND,
                [
                    'email'       => $email,
                    'contact'     => $contact,
                    'tokens'      => $tokens,
                    'contentHash' => '8bb9181fa1c76671352b79565e244240',
                    'idHash'      => '5cdaa50e155f9732391159',
                    'content'     => '<!DOCTYPE html><html>...',
                    'subject'     => 'Test Email',
                    'source'      => $source,
                    'headers'     => $headers,
                ]
            );

        $this->subscriber->onEmailSend($event);
    }
}
