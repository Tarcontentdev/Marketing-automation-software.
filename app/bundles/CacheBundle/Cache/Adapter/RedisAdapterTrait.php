<?php

declare(strict_types=1);

namespace MailVotech\CacheBundle\Cache\Adapter;

use MailVotech\CacheBundle\Exceptions\InvalidArgumentException;
use MailVotech\CoreBundle\Helper\PRedisConnectionHelper;
use Predis\Client;

trait RedisAdapterTrait
{
    /**
     * @param mixed[] $servers
     */
    private function createClient(array $servers, bool $primaryOnly): Client
    {
        if (!isset($servers['dsn'])) {
            throw new InvalidArgumentException('Invalid redis configuration. No server specified.');
        }

        $options                = array_key_exists('options', $servers) ? $servers['options'] : [];
        $options['primaryOnly'] = $primaryOnly;

        return PRedisConnectionHelper::createClient(PRedisConnectionHelper::getRedisEndpoints($servers['dsn']), $options);
    }
}
