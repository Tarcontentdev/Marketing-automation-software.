<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Tests\Unit\Sync\SyncDataExchange\Internal;

use MailVotech\IntegrationsBundle\Event\InternalObjectEvent;
use MailVotech\IntegrationsBundle\IntegrationEvents;
use MailVotech\IntegrationsBundle\Sync\Exception\ObjectNotFoundException;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\ObjectProvider;
use MailVotech\LeadBundle\Entity\Lead;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ObjectProviderTest extends TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject&EventDispatcherInterface
     */
    private \PHPUnit\Framework\MockObject\MockObject $dispatcher;

    private ObjectProvider $objectProvider;

    protected function setUp(): void
    {
        $this->dispatcher     = $this->createMock(EventDispatcherInterface::class);
        $this->objectProvider = new ObjectProvider($this->dispatcher);
    }

    public function testGetObjectByNameIfItDoesNotExist(): void
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(InternalObjectEvent::class),
                IntegrationEvents::INTEGRATION_COLLECT_INTERNAL_OBJECTS
            );

        $this->expectException(ObjectNotFoundException::class);
        $this->objectProvider->getObjectByName('Unicorn');
    }

    public function testGetObjectByNameIfItExists(): void
    {
        $contact = new Contact();
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(function (InternalObjectEvent $e) use ($contact): true {
                    // Fake a subscriber.
                    $e->addObject($contact);

                    return true;
                }),
                IntegrationEvents::INTEGRATION_COLLECT_INTERNAL_OBJECTS
            );

        $this->assertSame($contact, $this->objectProvider->getObjectByName(Contact::NAME));
    }

    public function testGetObjectByEntityNameIfItDoesNotExist(): void
    {
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->isInstanceOf(InternalObjectEvent::class),
                IntegrationEvents::INTEGRATION_COLLECT_INTERNAL_OBJECTS,
            );

        $this->expectException(ObjectNotFoundException::class);
        $this->objectProvider->getObjectByEntityName('Unicorn');
    }

    public function testGetObjectByEntityNameIfItExists(): void
    {
        $contact = new Contact();
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(function (InternalObjectEvent $e) use ($contact): true {
                    // Fake a subscriber.
                    $e->addObject($contact);

                    return true;
                }),
                IntegrationEvents::INTEGRATION_COLLECT_INTERNAL_OBJECTS
            );

        $this->assertSame($contact, $this->objectProvider->getObjectByEntityName(Lead::class));
    }
}
