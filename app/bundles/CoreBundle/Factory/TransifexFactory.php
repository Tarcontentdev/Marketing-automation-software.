<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Factory;

use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Http\Factory\Guzzle\UriFactory;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\Transifex\Config;
use MailVotech\Transifex\Exception\InvalidConfigurationException;
use MailVotech\Transifex\Transifex;
use MailVotech\Transifex\TransifexInterface;
use Psr\Http\Client\ClientInterface;

final class TransifexFactory
{
    private ?TransifexInterface $transifex = null;

    public function __construct(
        private readonly ClientInterface $client,
        private readonly CoreParametersHelper $coreParametersHelper,
    ) {
    }

    /**
     * @throws InvalidConfigurationException
     */
    public function getTransifex(): TransifexInterface
    {
        if (!$this->transifex) {
            $this->transifex = $this->create($this->coreParametersHelper->get('transifex_api_token') ?? '');
        }

        return $this->transifex;
    }

    /**
     * @throws InvalidConfigurationException
     */
    private function create(string $apiToken): TransifexInterface
    {
        $config = new Config();
        $config->setApiToken($apiToken);
        $config->setOrganization('mailvotech');
        $config->setProject('mailvotech');

        return new Transifex($this->client, new RequestFactory(), new StreamFactory(), new UriFactory(), $config);
    }
}
