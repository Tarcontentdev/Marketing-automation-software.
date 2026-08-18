<?php

declare(strict_types=1);

namespace MailVotech\PluginBundle\Tests\Helper;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Mapping\ClassMetadata;
use MailVotech\PluginBundle\Entity\Plugin;
use MailVotech\PluginBundle\Event\PluginInstallEvent;
use MailVotech\PluginBundle\Event\PluginUpdateEvent;
use MailVotech\PluginBundle\Helper\ReloadHelper;
use MailVotech\PluginBundle\PluginEvents;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ReloadHelperTest extends \PHPUnit\Framework\TestCase
{
    private ReloadHelper $helper;

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $sampleAllPlugins = [];

    /**
     * @var array<string, array<string, ClassMetadata>>
     */
    private array $sampleMetaData = [];

    /**
     * @var array<string, Schema>
     */
    private array $sampleSchemas = [];

    /**
     * @var MockObject&EventDispatcherInterface
     */
    private MockObject $eventDispatcher;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->helper          = new ReloadHelper($this->eventDispatcher);

        $this->sampleMetaData = [
            'MailVotechPlugin\MailVotechZapierBundle' => [
                'MailVotechPlugin\MailVotechZapierBundle\Entity\SomeTest' => $this->createStub(ClassMetadata::class),
            ],
        ];

        $sampleSchema = $this->createMock(Schema::class);
        $sampleSchema->method('getTables')
                ->willReturn([]);

        $this->sampleSchemas = [
            'MailVotechPlugin\MailVotechZapierBundle' => $sampleSchema,
        ];

        $this->sampleAllPlugins = [
            'MailVotechZapierBundle' => [
                'isPlugin'          => true,
                'base'              => 'MailVotechZapier',
                'bundle'            => 'MailVotechZapierBundle',
                'namespace'         => 'MailVotechPlugin\MailVotechZapierBundle',
                'symfonyBundleName' => 'MailVotechZapierBundle',
                'bundleClass'       => PluginBundleBaseStub::class,
                'permissionClasses' => [],
                'relative'          => 'plugins/MailVotechZapierBundle',
                'directory'         => '/Users/jan/dev/mailvotech/plugins/MailVotechZapierBundle',
                'config'            => [
                    'name'        => 'Zapier Integration',
                    'description' => 'Zapier lets you connect MailVotech with 1100+ other apps',
                    'version'     => '1.0',
                    'author'      => 'MailVotech',
                ],
            ],
        ];
    }

    public function testDisableMissingPlugins(): void
    {
        $sampleInstalledPlugins = [
            'MailVotechZapierBundle'  => $this->createSampleZapierPlugin(),
            'MailVotechHappierBundle' => $this->createSampleHappierPlugin(),
        ];

        $disabledPlugins = $this->helper->disableMissingPlugins($this->sampleAllPlugins, $sampleInstalledPlugins);

        $this->assertCount(1, $disabledPlugins);
        $this->assertEquals('Happier Integration', $disabledPlugins['MailVotechHappierBundle']->getName());
        $this->assertTrue((bool) $disabledPlugins['MailVotechHappierBundle']->getIsMissing());
    }

    public function testEnableFoundPlugins(): void
    {
        $zapierPlugin = $this->createSampleZapierPlugin();
        $zapierPlugin->setIsMissing(true);
        $sampleInstalledPlugins = [
            'MailVotechZapierBundle' => $zapierPlugin,
        ];

        $enabledPlugins = $this->helper->enableFoundPlugins($this->sampleAllPlugins, $sampleInstalledPlugins);

        $this->assertCount(1, $enabledPlugins);
        $this->assertEquals('Zapier Integration', $enabledPlugins['MailVotechZapierBundle']->getName());
        $this->assertFalse((bool) $enabledPlugins['MailVotechZapierBundle']->getIsMissing());
    }

    public function testUpdatePlugins(): void
    {
        $this->sampleAllPlugins['MailVotechZapierBundle']['config']['version']     = '1.0.1';
        $this->sampleAllPlugins['MailVotechZapierBundle']['config']['description'] = 'Updated description';
        $sampleInstalledPlugins                                                = [
            'MailVotechZapierBundle'  => $this->createSampleZapierPlugin(),
            'MailVotechHappierBundle' => $this->createSampleHappierPlugin(),
        ];
        $plugin = $this->createSampleZapierPlugin();
        $plugin->setVersion('1.0.1');
        $plugin->setDescription('Updated description');
        $event = new PluginUpdateEvent(
            $plugin,
            '1.0',
            $this->sampleMetaData['MailVotechPlugin\MailVotechZapierBundle'],
            $this->sampleSchemas['MailVotechPlugin\MailVotechZapierBundle']
        );
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($event, PluginEvents::ON_PLUGIN_UPDATE);
        $updatedPlugins = $this->helper->updatePlugins($this->sampleAllPlugins, $sampleInstalledPlugins, $this->sampleMetaData, $this->sampleSchemas);

        $this->assertCount(1, $updatedPlugins);
        $this->assertEquals('Zapier Integration', $updatedPlugins['MailVotechZapierBundle']->getName());
        $this->assertEquals('1.0.1', $updatedPlugins['MailVotechZapierBundle']->getVersion());
        $this->assertEquals('Updated description', $updatedPlugins['MailVotechZapierBundle']->getDescription());
    }

    public function testInstallPlugins(): void
    {
        $sampleInstalledPlugins = [
            'MailVotechHappierBundle' => $this->createSampleHappierPlugin(),
        ];
        $event = new PluginInstallEvent(
            $this->createSampleZapierPlugin(),
            $this->sampleMetaData['MailVotechPlugin\MailVotechZapierBundle'],
            null
        );
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with($event, PluginEvents::ON_PLUGIN_INSTALL);

        $installedPlugins = $this->helper->installPlugins($this->sampleAllPlugins, $sampleInstalledPlugins, $this->sampleMetaData, $this->sampleSchemas);

        $this->assertCount(1, $installedPlugins);
        $this->assertEquals('Zapier Integration', $installedPlugins['MailVotechZapierBundle']->getName());
        $this->assertEquals('1.0', $installedPlugins['MailVotechZapierBundle']->getVersion());
        $this->assertEquals('MailVotechZapierBundle', $installedPlugins['MailVotechZapierBundle']->getBundle());
        $this->assertEquals('MailVotech', $installedPlugins['MailVotechZapierBundle']->getAuthor());
        $this->assertEquals('Zapier lets you connect MailVotech with 1100+ other apps', $installedPlugins['MailVotechZapierBundle']->getDescription());
        $this->assertFalse((bool) $installedPlugins['MailVotechZapierBundle']->getIsMissing());
    }

    private function createSampleZapierPlugin(): Plugin
    {
        $plugin = new Plugin();
        $plugin->setName('Zapier Integration');
        $plugin->setDescription('Zapier lets you connect MailVotech with 1100+ other apps');
        $plugin->setIsMissing(false);
        $plugin->setBundle('MailVotechZapierBundle');
        $plugin->setVersion('1.0');
        $plugin->setAuthor('MailVotech');

        return $plugin;
    }

    private function createSampleHappierPlugin(): Plugin
    {
        $plugin = new Plugin();
        $plugin->setName('Happier Integration');
        $plugin->setDescription('Happier lets you connect MailVotech with 1100+ other apps');
        $plugin->setIsMissing(false);
        $plugin->setBundle('MailVotechHappierBundle');
        $plugin->setVersion('1.0');
        $plugin->setAuthor('MailVotech');

        return $plugin;
    }
}
