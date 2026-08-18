<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Functional\EventListener;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\CoreBundle\Tests\Functional\UserEntityTrait;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\PageBundle\Event\UrlTokenReplaceEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class OwnerSubscriberFunctionalTest extends MailVotechMysqlTestCase
{
    use UserEntityTrait;

    private EventDispatcherInterface $dispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dispatcher = $this->getContainer()->get(EventDispatcherInterface::class);
    }

    public function testUrlTokenReplaceEventReplacesOwnerFieldToken(): void
    {
        $role  = $this->createRole(sprintf('Owner Role %s', uniqid()));
        $owner = $this->createUser(
            sprintf('owner-%s@example.com', uniqid()),
            sprintf('owner-%s', uniqid()),
            'Adrian',
            'Owner',
            $role
        );

        $lead = new Lead();
        $lead->setEmail(sprintf('contact-%s@example.com', uniqid()));
        $lead->setOwner($owner);

        $this->em->persist($lead);
        $this->em->flush();
        $this->em->clear();

        $lead = $this->em->getRepository(Lead::class)->find($lead->getId());
        $this->assertInstanceOf(Lead::class, $lead);

        $event = new UrlTokenReplaceEvent('https://example.mailvotech/author/{ownerfield=firstname}/', $lead);
        $this->dispatcher->dispatch($event);

        $this->assertSame('https://example.mailvotech/author/Adrian/', $event->getContent());
    }

    public function testUrlTokenReplaceEventBlanksOwnerFieldTokenWhenOwnerIsMissing(): void
    {
        $lead = new Lead();
        $lead->setEmail(sprintf('contact-%s@example.com', uniqid()));

        $this->em->persist($lead);
        $this->em->flush();
        $this->em->clear();

        $lead = $this->em->getRepository(Lead::class)->find($lead->getId());
        $this->assertInstanceOf(Lead::class, $lead);

        $event = new UrlTokenReplaceEvent('https://example.mailvotech/author/{ownerfield=firstname}/', $lead);
        $this->dispatcher->dispatch($event);

        $this->assertSame('https://example.mailvotech/author//', $event->getContent());
    }
}
