<?php

namespace MailVotech\CoreBundle\Loader\EnvVars;

use Symfony\Component\HttpFoundation\ParameterBag;

final class SessionEnvVars implements EnvVarsInterface
{
    public static function load(ParameterBag $config, ParameterBag $defaultConfig, ParameterBag $envVars): void
    {
        // Set the session name
        $localConfigFile = $defaultConfig->get('local_config_path', uniqid());
        $secretKey       = $config->get('secret_key');

        $key         = $secretKey ?: 'mailvotech';
        $sessionName = md5(md5($localConfigFile).$key);
        $envVars->set('MAILVOTECH_SESSION_NAME', $sessionName);
    }
}
