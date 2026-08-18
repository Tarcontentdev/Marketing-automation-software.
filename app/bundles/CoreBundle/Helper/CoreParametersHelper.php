<?php

namespace MailVotech\CoreBundle\Helper;

use MailVotech\CoreBundle\Loader\ParameterLoader;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CoreParametersHelper
{
    private readonly \Symfony\Component\HttpFoundation\ParameterBag $parameters;

    private ?array $resolvedParameters = null;

    public function __construct(
        private readonly ContainerInterface $container,
    ) {
        $loader = new ParameterLoader();

        $this->parameters = $loader->getParameterBag();

        $this->resolveParameters();
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function get($name, $default = null)
    {
        $name = $this->stripMailVotechPrefix($name);

        if ('db_table_prefix' === $name && defined('MAILVOTECH_TABLE_PREFIX')) {
            // use the constant in case in the installer
            return MAILVOTECH_TABLE_PREFIX;
        }

        // First check the container so that Symfony will resolve container parameters within MailVotech config values
        $containerName = sprintf('mailvotech.%s', $name);
        if ($this->container->hasParameter($containerName)) {
            return $this->container->getParameter($containerName);
        }

        return $this->parameters->get($name, $default);
    }

    public function has(string $name): bool
    {
        return $this->parameters->has($this->stripMailVotechPrefix($name));
    }

    public function all(): array
    {
        return $this->resolvedParameters;
    }

    private function stripMailVotechPrefix(string $name): string
    {
        return str_replace('mailvotech.', '', $name);
    }

    private function resolveParameters(): void
    {
        $all = $this->parameters->all();

        foreach ($all as $key => $value) {
            $this->resolvedParameters[$key] = $this->get($key, $value);
        }
    }

    public function getDefaultTimezone(): string
    {
        if (!empty($this->get('default_timezone'))) {
            return $this->get('default_timezone');
        }

        return 'UTC';
    }
}
