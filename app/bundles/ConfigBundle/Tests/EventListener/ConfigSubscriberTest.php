<?php

declare(strict_types=1);

namespace MailVotech\ConfigBundle\Tests\EventListener;

use MailVotech\ConfigBundle\ConfigEvents;
use MailVotech\ConfigBundle\Event\ConfigEvent;
use MailVotech\ConfigBundle\EventListener\ConfigSubscriber;
use MailVotech\ConfigBundle\Service\ConfigChangeLogger;
use MailVotech\CoreBundle\Entity\AuditLogRepository;
use MailVotech\CoreBundle\Entity\IpAddressRepository;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ConfigSubscriberTest extends TestCase
{
    /**
     * @var MockObject&ConfigChangeLogger
     */
    private MockObject $logger;

    private ConfigSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->logger     = $this->createMock(ConfigChangeLogger::class);
        $this->subscriber = new ConfigSubscriber($this->logger, $this->createStub(IpAddressRepository::class), $this->createStub(CoreParametersHelper::class), $this->createStub(AuditLogRepository::class));
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                ConfigEvents::CONFIG_POST_SAVE => ['onConfigPostSave', 0],
            ],
            $this->subscriber->getSubscribedEvents()
        );
    }

    public function testNothingToLogOnConfigPostSave(): void
    {
        // Test nothing to log
        $this->logger->expects($this->never())
            ->method('log');
        $event = $this->createMock(ConfigEvent::class);
        $event->expects($this->once())
            ->method('getOriginalNormData')
            ->willReturn(null);

        $this->subscriber->onConfigPostSave($event);
    }

    public function testSomethingToLogOnConfigPostSave(): void
    {
        // Test something to log
        $originalNormData = ['orig'];
        $normData         = ['norm'];

        $event = $this->createMock(ConfigEvent::class);
        $event->expects($this->once())
            ->method('getOriginalNormData')
            ->willReturn($originalNormData);
        $event->expects($this->once())
            ->method('getNormData')
            ->willReturn($normData);
        $this->logger->expects($this->once())
            ->method('setOriginalNormData')
            ->with($originalNormData)
            ->willReturn($this->logger);
        $this->logger->expects($this->once())
            ->method('log')
            ->with($normData);

        $this->subscriber->onConfigPostSave($event);
    }
}
