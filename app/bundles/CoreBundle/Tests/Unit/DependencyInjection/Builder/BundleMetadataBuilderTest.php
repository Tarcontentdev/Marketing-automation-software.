<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\DependencyInjection\Builder;

use MailVotech\CoreBundle\DependencyInjection\Builder\BundleMetadataBuilder;
use MailVotech\CoreBundle\MailVotechCoreBundle;
use MailVotech\CoreBundle\Security\Permissions\SystemPermissions;
use MailVotechPlugin\MailVotechFocusBundle\MailVotechFocusBundle;
use MailVotechPlugin\MailVotechFocusBundle\Security\Permissions\FocusPermissions;
use PHPUnit\Framework\TestCase;

final class BundleMetadataBuilderTest extends TestCase
{
    /**
     * @var array<string, string>
     */
    private array $paths;

    protected function setUp(): void
    {
        // Used in paths_helper
        $root        = __DIR__.'/../../../../../../../app';
        $projectRoot = __DIR__.'/../../../../../../../';

        $paths = [];
        include __DIR__.'/../../../../../../config/paths_helper.php';

        $this->paths = $paths;
    }

    public function testCoreBundleMetadataLoaded(): void
    {
        $bundles = ['MailVotechCoreBundle' => MailVotechCoreBundle::class];

        $builder  = new BundleMetadataBuilder($bundles, $this->paths);
        $metadata = $builder->getCoreBundleMetadata();

        $this->assertSame([], $builder->getPluginMetadata());
        $this->assertArrayHasKey('MailVotechCoreBundle', $metadata);

        $bundleMetadata = $metadata['MailVotechCoreBundle'];

        $this->assertFalse($bundleMetadata['isPlugin']);
        $this->assertEquals('Core', $bundleMetadata['base']);
        $this->assertEquals('CoreBundle', $bundleMetadata['bundle']);
        $this->assertEquals('MailVotechCoreBundle', $bundleMetadata['symfonyBundleName']);
        $this->assertEquals('app/bundles/CoreBundle', $bundleMetadata['relative']);
        $this->assertEquals(realpath($this->paths['root']).'/app/bundles/CoreBundle', $bundleMetadata['directory']);
        $this->assertEquals('MailVotech\CoreBundle', $bundleMetadata['namespace']);
        $this->assertEquals(MailVotechCoreBundle::class, $bundleMetadata['bundleClass']);
        $this->assertArrayHasKey('permissionClasses', $bundleMetadata);
        $this->assertArrayHasKey(SystemPermissions::class, $bundleMetadata['permissionClasses']);
        $this->assertArrayHasKey('config', $bundleMetadata);
        $this->assertArrayHasKey('routes', $bundleMetadata['config']);
    }

    public function testPluginMetadataLoaded(): void
    {
        $bundles = ['MailVotechFocusBundle' => MailVotechFocusBundle::class];

        $builder  = new BundleMetadataBuilder($bundles, $this->paths);
        $metadata = $builder->getPluginMetadata();

        $this->assertSame([], $builder->getCoreBundleMetadata());
        $this->assertArrayHasKey('MailVotechFocusBundle', $metadata);
        $bundleMetadata = $metadata['MailVotechFocusBundle'];

        $this->assertTrue($bundleMetadata['isPlugin']);
        $this->assertEquals('MailVotechFocus', $bundleMetadata['base']);
        $this->assertEquals('MailVotechFocusBundle', $bundleMetadata['bundle']);
        $this->assertEquals('MailVotechFocusBundle', $bundleMetadata['symfonyBundleName']);
        $this->assertEquals('plugins/MailVotechFocusBundle', $bundleMetadata['relative']);
        $this->assertEquals(realpath($this->paths['root']).'/plugins/MailVotechFocusBundle', $bundleMetadata['directory']);
        $this->assertEquals('MailVotechPlugin\MailVotechFocusBundle', $bundleMetadata['namespace']);
        $this->assertEquals(MailVotechFocusBundle::class, $bundleMetadata['bundleClass']);
        $this->assertArrayHasKey('permissionClasses', $bundleMetadata);
        $this->assertArrayHasKey(FocusPermissions::class, $bundleMetadata['permissionClasses']);
        $this->assertArrayHasKey('config', $bundleMetadata);
        $this->assertArrayHasKey('routes', $bundleMetadata['config']);
    }

    public function testSymfonyBundleIgnored(): void
    {
        $bundles = ['FooBarBundle' => 'Foo\Bar\BarBundle'];

        $builder = new BundleMetadataBuilder($bundles, $this->paths);
        $this->assertSame([], $builder->getCoreBundleMetadata());
        $this->assertSame([], $builder->getPluginMetadata());
    }
}
