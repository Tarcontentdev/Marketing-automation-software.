<?php

declare(strict_types=1);

use MailVotech\CoreBundle\DependencyInjection\MailVotechCoreExtension;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $configurator): void {
    $services = $configurator->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $services->load('MailVotech\\CacheBundle\\', '../')
        ->exclude('../{'.implode(',', MailVotechCoreExtension::DEFAULT_EXCLUDES).'}');
    $services->set('mailvotech.cache.adapter.filesystem', MailVotech\CacheBundle\Cache\Adapter\FilesystemTagAwareAdapter::class)
        ->arg('$prefix', param('mailvotech.cache_prefix'))
        ->arg('$lifetime', param('mailvotech.cache_lifetime'))
        ->arg('$directory', param('mailvotech.tmp_path'))
        ->tag('mailvotech.cache.adapter');
    $services->alias(MailVotech\CacheBundle\Cache\Adapter\FilesystemTagAwareAdapter::class, 'mailvotech.cache.adapter.filesystem');
    $services->set('mailvotech.cache.adapter.memcached', MailVotech\CacheBundle\Cache\Adapter\MemcachedTagAwareAdapter::class)
        ->arg('$servers', param('mailvotech.cache_adapter_memcached'))
        ->arg('$namespace', param('mailvotech.cache_prefix'))
        ->arg('$lifetime', param('mailvotech.cache_lifetime'))
        ->tag('mailvotech.cache.adapter');
    $services->alias(MailVotech\CacheBundle\Cache\Adapter\MemcachedTagAwareAdapter::class, 'mailvotech.cache.adapter.memcached');
    $services->set('mailvotech.cache.clear_cache_subscriber', MailVotech\CacheBundle\EventListener\CacheClearSubscriber::class)
        ->arg('$cacheProvider', service('mailvotech.cache.provider'))
        ->arg('$logger', service('monolog.logger.mailvotech'))
        ->tag('kernel.cache_clearer');
    $services->alias(MailVotech\CacheBundle\EventListener\CacheClearSubscriber::class, 'mailvotech.cache.clear_cache_subscriber');

    $services->alias(MailVotech\CacheBundle\Cache\CacheProviderInterface::class, MailVotech\CacheBundle\Cache\CacheProvider::class);
    $services->alias('mailvotech.cache.provider', MailVotech\CacheBundle\Cache\CacheProvider::class);
    $services->alias('mailvotech.cache.provider_tag_aware', MailVotech\CacheBundle\Cache\CacheProviderTagAware::class);
    $services->alias('mailvotech.cache.adapter.redis', MailVotech\CacheBundle\Cache\Adapter\RedisAdapter::class);
    $services->alias('mailvotech.cache.adapter.redis_tag_aware', MailVotech\CacheBundle\Cache\Adapter\RedisTagAwareAdapter::class);

    $services->get(MailVotech\CacheBundle\Cache\Adapter\RedisAdapter::class)
        ->tag('mailvotech.cache.adapter');
    $services->get(MailVotech\CacheBundle\Cache\Adapter\RedisTagAwareAdapter::class)
        ->tag('mailvotech.cache.adapter');
};
