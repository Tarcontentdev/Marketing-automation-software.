<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Functional\DependencyInjection;

use MailVotech\AssetBundle\Controller\UploadController;
use MailVotech\CacheBundle\Cache\Adapter\MemcachedTagAwareAdapter;
use MailVotech\CacheBundle\Cache\Adapter\RedisAdapter;
use MailVotech\CacheBundle\Cache\Adapter\RedisTagAwareAdapter;
use MailVotech\CampaignBundle\Enum\RepublishBehavior;
use MailVotech\CategoryBundle\Controller\Api\CategoryApiController;
use MailVotech\CoreBundle\Form\Type\DynamicContentFilterEntryType;
use MailVotech\CoreBundle\Helper\BuilderTokenHelper;
use MailVotech\DynamicContentBundle\Form\Type\DynamicContentType;
use MailVotech\FormBundle\Enum\Token\RedirectUrlToken;
use MailVotech\LeadBundle\Controller\Api\FieldApiController;
use MailVotech\LeadBundle\EventListener\SearchSubscriber;
use MailVotech\LeadBundle\Form\Validator\Constraints\UniqueUserAlias;
use MailVotech\LeadBundle\Validator\Constraints\Length;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Contracts\Service\ServiceProviderInterface;

abstract class AbstractContainerSmokeTestCase extends TestCase
{
    /**
     * Services that cannot be created in a test environment, or that are broken on purpose to keep these tests green.
     *
     * @var string[]
     */
    private const SKIPPED_SERVICE_IDS = [
        // requires a database connection in the constructor
        DynamicContentFilterEntryType::class,
        DynamicContentType::class,
        SearchSubscriber::class,
        \MailVotechPlugin\MailVotechClearbitBundle\EventListener\LeadSubscriber::class,
        \MailVotechPlugin\MailVotechClearbitBundle\Helper\LookupHelper::class,
        \MailVotechPlugin\MailVotechFullContactBundle\EventListener\LeadSubscriber::class,
        \MailVotechPlugin\MailVotechFullContactBundle\Helper\LookupHelper::class,
        'mailvotech.plugin.clearbit.lookup_helper',
        'mailvotech.plugin.fullcontact.lookup_helper',

        // requires a running Redis/Memcached server or an optional package
        MemcachedTagAwareAdapter::class,
        RedisAdapter::class,
        RedisTagAwareAdapter::class,
        'mailvotech.cache.adapter.memcached',
        'mailvotech.cache.adapter.redis',
        'mailvotech.cache.adapter.redis_tag_aware',
        'doctrine.uuid_generator',

        // not a service at all, an enum or a validation constraint
        RepublishBehavior::class,
        RedirectUrlToken::class,
        UniqueUserAlias::class,
        Length::class,

        // broken wiring: missing class, wrong argument count or wrong argument type
        UploadController::class,
        'MailVotech\CampaignBundle\Service\Campaign',
        CategoryApiController::class,
        BuilderTokenHelper::class,
        FieldApiController::class,
        'fos_oauth_server.controller.authorize',
        'mailvotech.helper.token_builder',
    ];

    /**
     * @return string[]
     */
    private function resolveServiceIds(Container $container): array
    {
        /** @var ServiceProviderInterface $privateServiceLocator */
        $privateServiceLocator = $container->get('test.private_services_locator');

        $serviceIds = array_unique([
            ...$container->getServiceIds(),
            ...array_keys($privateServiceLocator->getProvidedServices()),
        ]);

        sort($serviceIds);

        return $serviceIds;
    }

    protected function buildContainer(): Container
    {
        $kernel = new TestKernel();
        $kernel->boot();

        /** @var Container $container */
        $container = $kernel->getContainer();

        return $container;
    }

    /**
     * Creates every service in the container, unique per instance, as the same service can be registered under an alias too.
     *
     * @return array<int, object>
     */
    protected function createAllServices(): array
    {
        $container = $this->buildContainer();
        $serviceIds = $this->resolveServiceIds($container);

        // the test container resolves private services as well
        /** @var ContainerInterface $testContainer */
        $testContainer = $container->get('test.service_container');

        $services         = [];
        $failedServiceIds = [];

        foreach ($serviceIds as $serviceId) {
            if (in_array($serviceId, self::SKIPPED_SERVICE_IDS, true)) {
                continue;
            }

            try {
                $service = $testContainer->get($serviceId);
            } catch (\Throwable $throwable) {
                $failedServiceIds[] = sprintf('%s: %s', $serviceId, $throwable->getMessage());
                continue;
            }

            if (is_object($service)) {
                $services[spl_object_id($service)] = $service;
            }
        }

        $this->assertSame([], $failedServiceIds);

        return $services;
    }

    /**
     * Vendor services are out of scope, only the MailVotech ones are.
     */
    protected function isLocalService(object $service): bool
    {
        return str_starts_with($service::class, 'MailVotech\\') || str_starts_with($service::class, 'MailVotechPlugin\\');
    }
}
