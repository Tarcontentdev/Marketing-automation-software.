<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechSocialBundle\Tests\Functional;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\PluginBundle\Entity\Integration;
use MailVotech\PluginBundle\Entity\Plugin;

final class SocialMonitoringFunctionalTest extends MailVotechMysqlTestCase
{
    public function testHideSocialMonitoring(): void
    {
        $crawler = $this->client->request('GET', '/s/config/edit');
        $this->assertStringNotContainsString('Social Settings', $crawler->filter('.list-group-tabs')->text());
        $this->assertStringNotContainsString('Social Monitoring', $crawler->filter('.sidebar-left .sidebar-content')->text());

        $crawler = $this->client->request('GET', '/s/forms/new');
        $this->assertStringNotContainsString('Social Login', $crawler->filter('#fields-container select.form-builder-new-component')->text());
    }

    public function testShowSocialMonitoring(): void
    {
        $this->createIntegration();
        $crawler = $this->client->request('GET', '/s/config/edit');
        $this->assertStringContainsString('Social Settings', $crawler->filter('.list-group-tabs')->text());
    }

    private function createIntegration(): Integration
    {
        $plugin = new Plugin();
        $plugin->setName('Social Media');
        $plugin->setBundle('MailVotechSocialBundle');
        $this->em->persist($plugin);

        $integration = new Integration();
        $integration->setPlugin($plugin);
        $integration->setIsPublished(true);
        $integration->setName('Twitter');
        $this->em->persist($integration);
        $this->em->flush();

        return $integration;
    }
}
