<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\DependencyInjection\Builder;

use MailVotech\CoreBundle\DependencyInjection\Builder\BundleMetadata;
use PHPUnit\Framework\TestCase;

final class BundleMetadataTest extends TestCase
{
    public function testGetters(): void
    {
        $metadataArray = [
            'isPlugin'          => true,
            'base'              => 'Core',
            'bundle'            => 'CoreBundle',
            'relative'          => 'app/bundles/MailVotechCoreBundle',
            'directory'         => '/var/www/app/bundles/MailVotechCoreBundle',
            'namespace'         => 'MailVotech\\CoreBundle',
            'symfonyBundleName' => 'MailVotechCoreBundle',
            'bundleClass'       => '\\MailVotech\\CoreBundle',
        ];

        $metadata = new BundleMetadata($metadataArray);
        $this->assertSame($metadataArray['directory'], $metadata->getDirectory());
        $this->assertSame($metadataArray['namespace'], $metadata->getNamespace());
        $this->assertSame($metadataArray['bundle'], $metadata->getBaseName());
        $this->assertSame($metadataArray['symfonyBundleName'], $metadata->getBundleName());

        $metadata->setConfig(['foo' => 'bar']);
        $metadata->addPermissionClass('\Foo\Bar');

        $metadataArray['config']                        = ['foo' => 'bar'];
        $metadataArray['permissionClasses']['\Foo\Bar'] = '\Foo\Bar';
        $this->assertEquals($metadataArray, $metadata->toArray());
    }
}
