<?php

declare(strict_types=1);

namespace MailVotech\MarketplaceBundle\DTO;

final class AllowlistEntry
{
    public function __construct(
        /**
         * Packagist package in the format vendor/package.
         */
        public string $package,
        /**
         * Human readable name.
         */
        public string $displayName,
        /**
         * Minimum MailVotech version in semver format (e.g. 4.1.2).
         */
        public ?string $minimumMailVotechVersion,
        /**
         * Maximum MailVotech version in semver format (e.g. 4.1.2).
         */
        public ?string $maximumMailVotechVersion,
    ) {
    }

    /**
     * @param array<string,mixed> $array
     */
    public static function fromArray(array $array): self
    {
        return new self(
            $array['package'],
            $array['display_name'] ?? '',
            $array['minimum_mailvotech_version'],
            $array['maximum_mailvotech_version']
        );
    }

    /**
     * @return array<string,string>
     */
    public function toArray(): array
    {
        return [
            'package'                => $this->package,
            'display_name'           => $this->displayName,
            'minimum_mailvotech_version' => $this->minimumMailVotechVersion,
            'maximum_mailvotech_version' => $this->maximumMailVotechVersion,
        ];
    }
}
