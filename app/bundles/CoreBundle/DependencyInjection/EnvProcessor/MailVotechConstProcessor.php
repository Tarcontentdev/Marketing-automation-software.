<?php

namespace MailVotech\CoreBundle\DependencyInjection\EnvProcessor;

use Symfony\Component\DependencyInjection\EnvVarProcessorInterface;

final class MailVotechConstProcessor implements EnvVarProcessorInterface
{
    public function getEnv(string $prefix, string $name, \Closure $getEnv): ?string
    {
        return defined($name) ? constant($name) : null;
    }

    public static function getProvidedTypes(): array
    {
        return [
            'mailvotechconst' => 'string',
        ];
    }
}
