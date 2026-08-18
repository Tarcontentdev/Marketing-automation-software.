<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechClearbitBundle\Tests\Unit\Integration;

use MailVotech\PluginBundle\Entity\Integration;
use MailVotechPlugin\MailVotechClearbitBundle\Integration\ClearbitIntegration;
use PHPUnit\Framework\TestCase;

final class ClearbitIntegrationTest extends TestCase
{
    private ClearbitIntegration $integration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integration = new ClearbitIntegration();
    }

    public function testGetNameReturnsClearbit(): void
    {
        $this->assertSame('Clearbit', $this->integration->getName());
    }

    public function testGetDisplayNameReturnsClearbit(): void
    {
        $this->assertSame('Clearbit', $this->integration->getDisplayName());
    }

    public function testGetIconReturnsExpectedPath(): void
    {
        $this->assertSame('plugins/MailVotechClearbitBundle/Assets/img/clearbit.png', $this->integration->getIcon());
    }

    public function testShouldAutoUpdateReturnsFalseWhenNoConfigurationSet(): void
    {
        $this->assertFalse($this->integration->shouldAutoUpdate());
    }

    public function testShouldAutoUpdateReturnsFalseWhenAutoUpdateKeyMissing(): void
    {
        $this->integration->setIntegrationConfiguration($this->makeConfiguration([]));

        $this->assertFalse($this->integration->shouldAutoUpdate());
    }

    public function testShouldAutoUpdateReturnsFalseWhenAutoUpdateDisabled(): void
    {
        $this->integration->setIntegrationConfiguration($this->makeConfiguration(['auto_update' => '0']));

        $this->assertFalse($this->integration->shouldAutoUpdate());
    }

    public function testShouldAutoUpdateReturnsTrueWhenAutoUpdateEnabled(): void
    {
        $this->integration->setIntegrationConfiguration($this->makeConfiguration(['auto_update' => '1']));

        $this->assertTrue($this->integration->shouldAutoUpdate());
    }

    /**
     * @param array<string, mixed> $apiKeys
     */
    private function makeConfiguration(array $apiKeys): Integration
    {
        $configuration = new Integration();
        $configuration->setApiKeys($apiKeys);

        return $configuration;
    }
}
