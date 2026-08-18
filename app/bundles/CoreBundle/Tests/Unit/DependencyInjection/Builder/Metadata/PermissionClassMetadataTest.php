<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\DependencyInjection\Builder\Metadata;

use MailVotech\AssetBundle\Security\Permissions\AssetPermissions;
use MailVotech\CoreBundle\DependencyInjection\Builder\BundleMetadata;
use MailVotech\CoreBundle\DependencyInjection\Builder\Metadata\PermissionClassMetadata;
use MailVotech\CoreBundle\Security\Permissions\SystemPermissions;
use PHPUnit\Framework\TestCase;

final class PermissionClassMetadataTest extends TestCase
{
    public function testPermissionsFound(): void
    {
        $metadataArray = [
            'isPlugin'          => false,
            'base'              => 'Core',
            'bundle'            => 'CoreBundle',
            'relative'          => 'app/bundles/MailVotechCoreBundle',
            'directory'         => __DIR__.'/../../../../../',
            'namespace'         => 'MailVotech\\CoreBundle',
            'symfonyBundleName' => 'MailVotechCoreBundle',
            'bundleClass'       => '\\MailVotech\\CoreBundle',
        ];

        $metadata                = new BundleMetadata($metadataArray);
        $permissionClassMetadata = new PermissionClassMetadata($metadata);
        $permissionClassMetadata->build();

        $this->assertArrayHasKey(SystemPermissions::class, $metadata->toArray()['permissionClasses']);
        $this->assertCount(1, $metadata->toArray()['permissionClasses']);
    }

    public function testCompatibilityWithPermissionServices(): void
    {
        $metadataArray = [
            'isPlugin'          => false,
            'base'              => 'Asset',
            'bundle'            => 'AssetBundle',
            'relative'          => 'app/bundles/MailVotechAssetBundle',
            'directory'         => __DIR__.'/../../../../../../AssetBundle',
            'namespace'         => 'MailVotech\\AssetBundle',
            'symfonyBundleName' => 'MailVotechAssetBundle',
            'bundleClass'       => '\\MailVotech\\AssetBundle',
        ];

        $metadata                = new BundleMetadata($metadataArray);
        $permissionClassMetadata = new PermissionClassMetadata($metadata);
        $permissionClassMetadata->build();

        $this->assertArrayHasKey(AssetPermissions::class, $metadata->toArray()['permissionClasses']);
    }
}
