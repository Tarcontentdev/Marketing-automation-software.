<?php

declare(strict_types=1);

namespace MailVotech\NotificationBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Handler\MockHandler;
use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\NotificationBundle\Entity\Notification;
use MailVotech\PluginBundle\Helper\IntegrationHelper;
use MailVotech\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\DependencyInjection\ContainerInterface;

trait NotificationTrait
{
    private MockHandler $transportMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->transportMock = $this->getMockHandler(static::getContainer());
        $this->setupIntegration(static::getContainer(), $this->em, self::API_ID, self::REST_API_ID);
    }

    private function getMockHandler(ContainerInterface $container): MockHandler
    {
        return $container->get(MockHandler::class);
    }

    private function createNotification(EntityManagerInterface $em): Notification
    {
        $notification = new Notification();
        $notification->setName('Name 1');
        $notification->setHeading('Heading 1');
        $notification->setMessage('Message 1');
        $em->persist($notification);

        return $notification;
    }

    private function createCampaign(EntityManagerInterface $em): Campaign
    {
        $campaign = new Campaign();
        $campaign->setName('Notification');
        $em->persist($campaign);

        return $campaign;
    }

    private function setupIntegration(ContainerInterface $container, EntityManagerInterface $em, string $apiId, string $restApiId): void
    {
        /** @var AbstractIntegration $integration */
        $integration = $container->get(IntegrationHelper::class)
            ->getIntegrationObject('OneSignal');
        $integrationSettings = $integration->getIntegrationSettings();
        $integrationSettings->setIsPublished(true);
        $integration->encryptAndSetApiKeys([
            'app_id'       => $apiId,
            'rest_api_key' => $restApiId,
        ], $integrationSettings);
        $em->persist($integrationSettings);
    }
}
