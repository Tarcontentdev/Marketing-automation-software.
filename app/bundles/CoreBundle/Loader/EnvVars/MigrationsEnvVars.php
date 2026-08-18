<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Loader\EnvVars;

use Symfony\Component\HttpFoundation\ParameterBag;

final class MigrationsEnvVars implements EnvVarsInterface
{
    public static function load(ParameterBag $config, ParameterBag $defaultConfig, ParameterBag $envVars): void
    {
        $prefix = $config->get('db_table_prefix');
        $envVars->set('MAILVOTECH_MIGRATIONS_TABLE_NAME', $prefix.'migrations');
    }
}
