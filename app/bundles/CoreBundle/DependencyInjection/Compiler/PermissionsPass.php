<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class PermissionsPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $corePermissions = $container->findDefinition('mailvotech.security');

        foreach ($container->findTaggedServiceIds('mailvotech.permissions') as $id => $tags) {
            $permissionObject = $container->findDefinition($id);
            $corePermissions->addMethodCall('setPermissionObject', [$permissionObject]);
        }
    }
}
