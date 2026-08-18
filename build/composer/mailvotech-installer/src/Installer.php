<?php

declare(strict_types=1);

namespace MailVotech\ComposerInstaller;

use Composer\Installer\LibraryInstaller;
use Composer\Package\PackageInterface;
use InvalidArgumentException;

final class Installer extends LibraryInstaller
{
    private const TYPE_CORE = 'mailvotech-core';
    private const TYPE_PLUGIN = 'mailvotech-plugin';
    private const TYPE_THEME = 'mailvotech-theme';

    public function supports(string $packageType): bool
    {
        return in_array(
            $packageType,
            [
                self::TYPE_CORE,
                self::TYPE_PLUGIN,
                self::TYPE_THEME,
            ],
            true
        );
    }

    public function getInstallPath(PackageInterface $package): string
    {
        return match ($package->getType()) {
            self::TYPE_CORE => $this->resolvePath(
                'core-path',
                'app',
                $package,
                false
            ),

            self::TYPE_PLUGIN => $this->resolvePath(
                'plugin-path',
                'plugins/{$name}',
                $package,
                true
            ),

            self::TYPE_THEME => $this->resolvePath(
                'theme-path',
                'themes/{$name}',
                $package,
                true
            ),

            default => throw new InvalidArgumentException(
                sprintf(
                    'Unsupported MailVotech package type "%s".',
                    $package->getType()
                )
            ),
        };
    }

    private function resolvePath(
        string $configKey,
        string $defaultPath,
        PackageInterface $package,
        bool $injectName
    ): string {
        $rootExtra = $this->composer->getPackage()->getExtra();

        $config = isset($rootExtra['mailvotech-installer'])
            && is_array($rootExtra['mailvotech-installer'])
            ? $rootExtra['mailvotech-installer']
            : [];

        $path = $config[$configKey] ?? $defaultPath;

        if (!is_string($path) || $path === '') {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid MailVotech installer path "%s".',
                    $configKey
                )
            );
        }

        if ($injectName) {
            $path = str_replace(
                '{$name}',
                $this->getDirectoryName($package),
                $path
            );
        }

        return rtrim($path, '/');
    }

    private function getDirectoryName(PackageInterface $package): string
    {
        $extra = $package->getExtra();

        if (
            isset($extra['install-directory-name'])
            && is_string($extra['install-directory-name'])
            && $extra['install-directory-name'] !== ''
        ) {
            return $extra['install-directory-name'];
        }

        $packageName = basename($package->getPrettyName());

        return str_replace(
            ' ',
            '',
            ucwords(
                str_replace('-', ' ', $packageName)
            )
        );
    }
}
