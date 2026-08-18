<?php

declare(strict_types=1);

namespace MailVotech\MarketplaceBundle\Service;

use GuzzleHttp\ClientInterface;
use MailVotech\CacheBundle\Cache\CacheProvider;
use MailVotech\MarketplaceBundle\DTO\Allowlist as DTOAllowlist;
use MailVotech\MarketplaceBundle\Exception\ApiException;

/**
 * Provides several helper functions to interact with MailVotech's allowlist.
 */
class Allowlist
{
    private const MARKETPLACE_ALLOWLIST_CACHE_KEY = 'marketplace_allowlist';

    public function __construct(
        private readonly Config $config,
        private readonly CacheProvider $cache,
        private readonly ClientInterface $httpClient,
    ) {
    }

    public function getAllowList(): ?DTOAllowlist
    {
        $cache                 = $this->cache->getSimpleCache();
        $cachedAllowlistString = $cache->get(self::MARKETPLACE_ALLOWLIST_CACHE_KEY);

        if (!empty($cachedAllowlistString)) {
            return $this->parseAllowlistJson($cachedAllowlistString);
        }

        if (!empty($this->config->getAllowListUrl())) {
            $response  = $this->httpClient->request('GET', $this->config->getAllowlistUrl());
            $body      = (string) $response->getBody();

            if ($response->getStatusCode() >= 300) {
                throw new ApiException($body, $response->getStatusCode());
            }

            // Cache the allowlist for the given amount of seconds (3600 by default).
            $cache->set(
                self::MARKETPLACE_ALLOWLIST_CACHE_KEY,
                $body,
                $this->config->getAllowlistCacheTtlSeconds()
            );

            return $this->parseAllowlistJson($body);
        }

        return null;
    }

    public function clearCache(): void
    {
        $this->cache->getSimpleCache()->delete(self::MARKETPLACE_ALLOWLIST_CACHE_KEY);
    }

    private function parseAllowlistJson(string $payload): DTOAllowlist
    {
        return DTOAllowlist::fromArray(json_decode($payload, true));
    }
}
