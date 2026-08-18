<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechTagManagerBundle\Tests\Unit\Integration;

use MailVotechPlugin\MailVotechTagManagerBundle\Integration\TagManagerIntegration;
use PHPUnit\Framework\TestCase;

final class TagManagerIntegrationTest extends TestCase
{
    private TagManagerIntegration $tagManagerIntegration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tagManagerIntegration = new TagManagerIntegration();
    }

    public function testGetNameReturnsName(): void
    {
        $name = $this->tagManagerIntegration->getName();
        $this->assertSame(TagManagerIntegration::PLUGIN_NAME, $name);
    }

    public function testGetDisplayNameReturnsName(): void
    {
        $displayName = $this->tagManagerIntegration->getDisplayName();
        $this->assertNotEmpty($displayName);
    }

    public function testGetIconReturnsExpectedPath(): void
    {
        $this->assertSame('plugins/MailVotechTagManagerBundle/Assets/img/tagmanager.png', $this->tagManagerIntegration->getIcon());
    }
}
