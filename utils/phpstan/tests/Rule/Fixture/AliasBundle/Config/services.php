<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Utils\PHPStan\Tests\Rule\Fixture\AliasBundle\UnusedAliasHelper;
use Utils\PHPStan\Tests\Rule\Fixture\AliasBundle\UsedAliasHelper;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services();

    $services->set('mailvotech.alias.used_helper', UsedAliasHelper::class);
    $services->alias(UsedAliasHelper::class, 'mailvotech.alias.used_helper');
    $services->alias('mailvotech.alias.legacy_used_helper', 'mailvotech.alias.used_helper');

    $services->set('mailvotech.alias.unused_helper', UnusedAliasHelper::class);
    $services->alias(UnusedAliasHelper::class, 'mailvotech.alias.unused_helper');
    $services->alias('mailvotech.alias.legacy_unused_helper', 'mailvotech.alias.unused_helper');
};
