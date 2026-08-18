<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\DependencyInjection\Builder\Metadata;

use MailVotech\CoreBundle\DependencyInjection\Builder\BundleMetadata;
use MailVotech\CoreBundle\DependencyInjection\Builder\Metadata\ConfigMetadata;
use MailVotech\CoreBundle\IpLookup\ExtremeIpLookup;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ConfigMetadataTest extends TestCase
{
    /**
     * @var BundleMetadata|MockObject
     */
    private MockObject $metadata;

    protected function setUp(): void
    {
        $this->metadata = $this->getMockBuilder(BundleMetadata::class)
            ->onlyMethods(['getDirectory'])
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testMissingConfigIsIgnored(): void
    {
        $this->metadata->expects($this->once())
            ->method('getDirectory')
            ->willReturn('/foo/bar');

        $configMetadata = new ConfigMetadata($this->metadata);
        $configMetadata->build();

        $this->assertEquals([], $this->metadata->toArray()['config']);
    }

    public function testBadConfigIsIgnored(): void
    {
        $this->metadata->expects($this->once())
            ->method('getDirectory')
            ->willReturn(__DIR__.'/resource/BadConfig');

        $configMetadata = new ConfigMetadata($this->metadata);
        $configMetadata->build();

        $this->assertEquals([], $this->metadata->toArray()['config']);
    }

    public function testIpLookupServicesAreLoaded(): void
    {
        $this->metadata->expects($this->once())
            ->method('getDirectory')
            ->willReturn(__DIR__.'/resource/GoodConfig');

        $configMetadata = new ConfigMetadata($this->metadata);
        $configMetadata->build();

        $this->assertSame(
            [
                'extreme-ip' => [
                    'display_name' => 'Extreme-IP',
                    'class'        => ExtremeIpLookup::class,
                ],
            ],
            $configMetadata->getIpLookupServices()
        );
    }

    public function testConfigIsLoaded(): void
    {
        $this->metadata->expects($this->once())
            ->method('getDirectory')
            ->willReturn(__DIR__.'/resource/GoodConfig');

        $configMetadata = new ConfigMetadata($this->metadata);
        $configMetadata->build();

        $config = $this->metadata->toArray()['config'];
        $this->assertArrayHasKey('mailvotech.helper.bundle', $config['services']['helpers']);
        $this->assertArrayHasKey('log_path', $config['parameters']);
    }

    public function testOptionalMissingServicesAreIgnored(): void
    {
        $this->metadata->expects($this->once())
            ->method('getDirectory')
            ->willReturn(__DIR__.'/resource/GoodConfig');

        $configMetadata = new ConfigMetadata($this->metadata);
        $configMetadata->build();

        $config = $this->metadata->toArray()['config'];
        $this->assertArrayNotHasKey('mailvotech.test.fixture', $config['services']['fixtures']);
    }

    public function testParameterArgumentsAreEncoded(): void
    {
        $this->metadata->expects($this->once())
            ->method('getDirectory')
            ->willReturn(__DIR__.'/resource/GoodConfig');

        $configMetadata = new ConfigMetadata($this->metadata);
        $configMetadata->build();

        $config = $this->metadata->toArray()['config'];
        $this->assertArrayHasKey('mailvotech.helper.bundle', $config['services']['helpers']);

        $this->assertEquals('%%mailvotech.bundles%%', $config['services']['helpers']['mailvotech.helper.bundle']['arguments'][0]);
    }

    public function testParametersAreEncoded(): void
    {
        $this->metadata->expects($this->once())
            ->method('getDirectory')
            ->willReturn(__DIR__.'/resource/GoodConfig');

        $configMetadata = new ConfigMetadata($this->metadata);
        $configMetadata->build();

        $config = $this->metadata->toArray()['config'];
        $this->assertArrayHasKey('log_path', $config['parameters']);

        $this->assertEquals('%%kernel.project_dir%%/var/logs', $config['parameters']['log_path']);
    }

    public function testParameterTypesArePreserved(): void
    {
        $this->metadata->expects($this->once())
            ->method('getDirectory')
            ->willReturn(__DIR__.'/resource/GoodConfig');

        $configMetadata = new ConfigMetadata($this->metadata);
        $configMetadata->build();

        $config = $this->metadata->toArray()['config'];
        $this->assertArrayHasKey('log_path', $config['parameters']);

        $this->assertEquals('%%kernel.project_dir%%/var/logs', $config['parameters']['log_path']);
        $this->assertEquals(7, $config['parameters']['max_log_files']);
        $this->assertEquals('media/images', $config['parameters']['image_path']);
        $this->assertEquals(false, $config['parameters']['bool_value']);
        $this->assertEquals(null, $config['parameters']['null_value']);
        $this->assertEquals([], $config['parameters']['array_value']);
    }
}
