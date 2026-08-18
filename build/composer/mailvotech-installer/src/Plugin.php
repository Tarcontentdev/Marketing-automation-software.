<?php

declare(strict_types=1);

namespace MailVotech\ComposerInstaller;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;

final class Plugin implements PluginInterface
{
    public function activate(Composer $composer, IOInterface $io): void
    {
        $composer
            ->getInstallationManager()
            ->addInstaller(
                new Installer($io, $composer)
            );
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
    }
}
