<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Loader\EnvVars;

use Symfony\Component\HttpFoundation\ParameterBag;

final class TwigEnvVars implements EnvVarsInterface
{
    public static function load(ParameterBag $config, ParameterBag $defaultConfig, ParameterBag $envVars): void
    {
        $tmpPath = $config->get('tmp_path');
        $envVars->set('MAILVOTECH_TWIG_CACHE_DIR', $tmpPath.'/twig');
    }
}
