<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\Loader\EnvVars;

use MailVotech\CoreBundle\Loader\EnvVars\SAMLEnvVars;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\ParameterBag;

final class SAMLEnvVarsTest extends TestCase
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

    public function testEntityIdIsSetToConfigIfNotEmpty(): void
    {
        $this->config->set('saml_idp_entity_id', 'foobar');
        SAMLEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('foobar', $this->envVars->get('MAILVOTECH_SAML_ENTITY_ID'));
    }

    public function testEntityIdIsSetToSiteUrlIfNotEmpty(): void
    {
        $this->config->set('saml_idp_entity_id', '');
        $this->config->set('site_url', 'https://foobar.com/happydays');

        SAMLEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('https://foobar.com', $this->envVars->get('MAILVOTECH_SAML_ENTITY_ID'));
    }

    public function testEntityIdIsSetToMailVotechByDefault(): void
    {
        $this->config->set('saml_idp_entity_id', '');
        $this->config->set('site_url', '');

        SAMLEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('mailvotech', $this->envVars->get('MAILVOTECH_SAML_ENTITY_ID'));
    }

    public function testLoginPathIsDefaultIfSamlIsDisabled(): void
    {
        $this->config->set('saml_idp_metadata', 'enabled');

        SAMLEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('/s/saml/login', $this->envVars->get('MAILVOTECH_SAML_LOGIN_PATH'));
        $this->assertEquals('/s/saml/login_check', $this->envVars->get('MAILVOTECH_SAML_LOGIN_CHECK_PATH'));
    }

    public function testCorrectLoginPathIfSamlIsEnabled(): void
    {
        $this->config->set('saml_idp_metadata', '');

        SAMLEnvVars::load($this->config, $this->defaultConfig, $this->envVars);

        $this->assertEquals('/s/saml/login', $this->envVars->get('MAILVOTECH_SAML_LOGIN_PATH'));
        $this->assertEquals('/s/saml/login_check', $this->envVars->get('MAILVOTECH_SAML_LOGIN_CHECK_PATH'));
    }
}
