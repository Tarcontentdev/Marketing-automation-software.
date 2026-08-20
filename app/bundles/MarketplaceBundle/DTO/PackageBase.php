<?php

declare(strict_types=1);

namespace MailVotech\MarketplaceBundle\DTO;

final class PackageBase
{
    public function __construct(
        /**
         * Original name in format "vendor/name".
         */
        public string $name,
        public string $url,
        public string $repository,
        public string $description,
        public int $downloads,
        public int $favers,
        /**
         * E.g. mailvotech-plugin.
         */
        public ?string $type,
        public ?string $displayName = null,
    ) {
    }

    public static function fromArray(array $array): self
    {
        return new self(
            $array['name'],
            $array['url'],
            $array['repository'],
            $array['description'],
            (int) $array['downloads'],
            (int) $array['favers'],
            $array['type'] ?? null,
            $array['display_name'] ?? null
        );
    }

    /**
     * Just an alias to getName(). Used in MailVotech helpers.
     */
    public function getId(): string
    {
        return $this->name;
    }

    /**
     * Used in MailVotech helpers.
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getPackageName(): string
    {
        [, $packageName] = explode('/', $this->name);

        return $packageName;
    }

    public function getHumanPackageName(): string
    {
        if ($this->displayName) {
            return $this->displayName;
        }

        $humanName = utf8_ucwords(str_replace('-', ' ', $this->getPackageName()));

        return str_replace('Mailvotech', 'MailVotech', $humanName);
    }

    public function getVendorName(): string
    {
        [$vendor] = explode('/', $this->name);

        return $vendor;
    }
}
