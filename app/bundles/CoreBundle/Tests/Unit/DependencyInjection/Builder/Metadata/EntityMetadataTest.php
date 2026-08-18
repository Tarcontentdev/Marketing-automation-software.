<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\DependencyInjection\Builder\Metadata;

use MailVotech\CoreBundle\DependencyInjection\Builder\BundleMetadata;
use MailVotech\CoreBundle\DependencyInjection\Builder\Metadata\EntityMetadata;
use PHPUnit\Framework\TestCase;

final class EntityMetadataTest extends TestCase
{
    private BundleMetadata $metadata;

    protected function setUp(): void
    {
        $metadataArray = [
            'isPlugin'          => true,
            'base'              => 'Core',
            'bundle'            => 'CoreBundle',
            'relative'          => 'app/bundles/MailVotechCoreBundle',
            'directory'         => __DIR__.'/../../../../../',
            'namespace'         => 'MailVotech\\CoreBundle',
            'symfonyBundleName' => 'MailVotechCoreBundle',
            'bundleClass'       => '\\MailVotech\\CoreBundle',
        ];

        $this->metadata = new BundleMetadata($metadataArray);
    }

    public function testOrmAndSerializerConfigsFound(): void
    {
        $entityMetadata = new EntityMetadata($this->metadata);
        $entityMetadata->build();

        $this->assertEquals(
            [
                'dir'       => 'Entity',
                'type'      => 'staticphp',
                'prefix'    => 'MailVotech\\CoreBundle\\Entity',
                'mapping'   => true,
                'is_bundle' => true,
            ],
            $entityMetadata->getOrmConfig()
        );

        $this->assertSame(
            [
                'namespace_prefix' => 'MailVotech\\CoreBundle\\Entity',
                'path'             => '@MailVotechCoreBundle/Entity',
            ],
            $entityMetadata->getSerializerConfig()
        );
    }
}
