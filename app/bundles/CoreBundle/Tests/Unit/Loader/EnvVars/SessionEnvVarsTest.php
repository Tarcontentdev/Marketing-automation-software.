<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\Loader\EnvVars;

use MailVotech\CoreBundle\Loader\EnvVars\SessionEnvVars;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

final class SessionEnvVarsTest extends TestCase
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

    public function testSessionNameIsCorrectlyGenerated(): void
    {
        $this->config->set('secret_key', 'topsecret');
        $this->defaultConfig->set('local_config_path', '/foo/bar');
        $sessionName = md5(md5('/foo/bar').'topsecret');

        SessionEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals($sessionName, $this->envVars->get('MAILVOTECH_SESSION_NAME'));
    }
}
