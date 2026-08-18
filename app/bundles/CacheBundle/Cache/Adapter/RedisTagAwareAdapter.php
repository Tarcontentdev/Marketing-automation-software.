<?php

declare(strict_types=1);

namespace MailVotech\CacheBundle\Cache\Adapter;

use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class RedisTagAwareAdapter extends TagAwareAdapter
{
    use RedisAdapterTrait;

    /**
     * @param mixed[] $servers
     */
    public function __construct(
        #[Autowire(env: 'json:MAILVOTECH_CACHE_ADAPTER_REDIS')]
        array $servers,

        #[Autowire(env: 'string:MAILVOTECH_CACHE_PREFIX')]
        string $namespace,

        #[Autowire(env: 'int:MAILVOTECH_CACHE_LIFETIME')]
        int $lifetime,

        #[Autowire(env: 'bool:MAILVOTECH_REDIS_PRIMARY_ONLY')]
        bool $primaryOnly,
    ) {
        $client = $this->createClient($servers, $primaryOnly);

        parent::__construct(
            new RedisAdapter($client, $namespace, $lifetime),
            new RedisAdapter($client, $namespace.'.tags.', $lifetime)
        );
    }
}
