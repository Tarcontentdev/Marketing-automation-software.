<?php

declare(strict_types=1);

namespace MailVotech\PluginBundle\Tests\EventListener;

use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Event\LeadEvent;
use MailVotech\PluginBundle\Entity\Integration;
use MailVotech\PluginBundle\Entity\IntegrationEntityRepository;
use MailVotech\PluginBundle\Entity\IntegrationRepository;
use MailVotech\PluginBundle\EventListener\LeadSubscriber;
use MailVotech\PluginBundle\Model\PluginModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LeadSubscriberTest extends TestCase
{
    private LeadSubscriber $subscriber;

    /**
     * @var MockObject&IntegrationEntityRepository
     */
    private MockObject $integrationEntityRepository;

    /**
     * @var MockObject&IntegrationRepository
     */
    private MockObject $integrationRepository;

    protected function setUp(): void
    {
        $pluginModel                       = $this->createMock(PluginModel::class);
        $this->integrationRepository       = $this->createMock(IntegrationRepository::class);
        $this->integrationEntityRepository = $this->createMock(IntegrationEntityRepository::class);
        $this->subscriber                  = new LeadSubscriber(
            $pluginModel,
            $this->integrationRepository
        );

        $pluginModel->expects($this->once())
            ->method('getIntegrationEntityRepository')
            ->willReturn($this->integrationEntityRepository);
    }

    public function testOnLeadSaveWithoutActiveIntegration(): void
    {
        $this->integrationRepository->expects($this->once())
            ->method('getIntegrations')
            ->willReturn([]);

        $this->integrationEntityRepository->expects($this->never())
            ->method('updateErrorLeads');

        $this->subscriber->onLeadSave(new LeadEvent(new Lead()));
    }

    public function testOnLeadSaveWithActiveIntegration(): void
    {
        $integration = new Integration();
        $integration->setIsPublished(true);
        $integration->setApiKeys(['key' => 'some']);
        $integration->setSupportedFeatures(['push_lead']);

        $this->integrationRepository->expects($this->once())
            ->method('getIntegrations')
            ->willReturn([$integration]);

        $this->integrationEntityRepository->expects($this->once())
            ->method('updateErrorLeads');

        $this->subscriber->onLeadSave(new LeadEvent(new Lead()));
    }
}
