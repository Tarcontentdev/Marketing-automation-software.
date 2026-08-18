<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechClearbitBundle\Integration\Support;

use MailVotech\IntegrationsBundle\Integration\DefaultConfigFormTrait;
use MailVotech\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthInterface;
use MailVotech\IntegrationsBundle\Integration\Interfaces\ConfigFormInterface;
use MailVotechPlugin\MailVotechClearbitBundle\Form\Type\ClearbitKeysType;
use MailVotechPlugin\MailVotechClearbitBundle\Integration\ClearbitIntegration;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

final class ConfigSupport extends ClearbitIntegration implements ConfigFormInterface, ConfigFormAuthInterface
{
    use DefaultConfigFormTrait;

    public function __construct(
        private RouterInterface $router,
    ) {
    }

    public function getAuthConfigFormName(): string
    {
        return ClearbitKeysType::class;
    }

    public function getConfigFormContentTemplate(): string
    {
        return '@MailVotechClearbit/Integration/config_form.html.twig';
    }

    public function getWebhookUrl(): string
    {
        return $this->router->generate('mailvotech_plugin_clearbit_index', [], UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
