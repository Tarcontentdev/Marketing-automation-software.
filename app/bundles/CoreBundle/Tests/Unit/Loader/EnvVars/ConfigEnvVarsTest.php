<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\Loader\EnvVars;

use MailVotech\CoreBundle\Loader\EnvVars\ConfigEnvVars;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

final class ConfigEnvVarsTest extends TestCase
{
    private ParameterBag $config;

    private ParameterBag $defaultConfig;

    private ParameterBag $envVars;

    protected function setUp(): void
    {
        $this->config        = new ParameterBag();
        $this->defaultConfig = new ParameterBag();
        $this->envVars       = new ParameterBag();
    }

    public function testGetEnvWorks(): void
    {
        putenv('MAILVOTECH_FOOBAR=bar');
        $this->config->set('foo', 'getenv(MAILVOTECH_FOOBAR)');

        ConfigEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('bar', $this->envVars->get('MAILVOTECH_FOO'));
    }

    public function testLocalValueIsSet(): void
    {
        $this->config->set('foo', 'bar');

        ConfigEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('bar', $this->envVars->get('MAILVOTECH_FOO'));
    }

    public function testValueIsJsonEncodedIfArray(): void
    {
        $this->config->set('foo', ['bar']);

        ConfigEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('["bar"]', $this->envVars->get('MAILVOTECH_FOO'));
    }

    public function testDefaultValueIsJsonEncodedIfArray(): void
    {
        $this->config->set('foo', null);
        $this->defaultConfig->set('foo', ['bar']);

        ConfigEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('["bar"]', $this->envVars->get('MAILVOTECH_FOO'));
    }
}
