<?php

namespace MailVotech\InstallBundle\Configurator\Step;

use MailVotech\CoreBundle\Configurator\Configurator;
use MailVotech\CoreBundle\Configurator\Step\StepInterface;
use MailVotech\CoreBundle\Helper\FileHelper;
use MailVotech\CoreBundle\Security\Cryptography\Cipher\Symmetric\OpenSSLCipher;
use MailVotech\InstallBundle\Configurator\Form\CheckStepType;
use Symfony\Component\HttpFoundation\RequestStack;

final class CheckStep implements StepInterface
{
    /**
     * Flag if the configuration file is writable.
     */
    private readonly bool $configIsWritable;

    /**
     * Absolute path to cache directory.
     * Required in step.
     *
     * @var string
     */
    public $cache_path = '%kernel.project_dir%/var/cache';

    /**
     * Absolute path to log directory.
     * Required in step.
     *
     * @var string
     */
    public $log_path = '%kernel.project_dir%/var/logs';

    /**
     * Set the domain URL for use in getting the absolute URL for cli/cronjob generated URLs.
     *
     * @var string
     */
    public $site_url = '';

    /**
     * Recommended minimum memory limit for MailVotech.
     *
     * @var string
     */
    public const RECOMMENDED_MEMORY_LIMIT = '512M';

    /**
     * @param Configurator $configurator Configurator service
     * @param string       $projectDir   Kernel root path
     * @param RequestStack $requestStack Request stack
     */
    public function __construct(
        Configurator $configurator,
        private readonly string $projectDir,
        RequestStack $requestStack,
        private readonly OpenSSLCipher $openSSLCipher,
    ) {
        $request = $requestStack->getCurrentRequest();

        $this->configIsWritable = $configurator->isFileWritable();
        if ($request instanceof \Symfony\Component\HttpFoundation\Request) {
            $this->site_url     = $request->getSchemeAndHttpHost().$request->getBasePath();
        }
    }

    public function getFormType(): string
    {
        return CheckStepType::class;
    }

    public function checkRequirements(): array
    {
        $messages = [];

        if (!is_dir($this->projectDir.'/vendor/composer')) {
            $messages[] = 'mailvotech.install.composer.dependencies';
        }

        if (!$this->configIsWritable) {
            $messages[] = 'mailvotech.install.config.unwritable';
        }

        if (!is_writable(str_replace('%kernel.project_dir%', $this->projectDir, $this->cache_path))) {
            $messages[] = 'mailvotech.install.cache.unwritable';
        }

        if (!is_writable(str_replace('%kernel.project_dir%', $this->projectDir, $this->log_path))) {
            $messages[] = 'mailvotech.install.logs.unwritable';
        }

        $timezones = [];

        foreach (\DateTimeZone::listAbbreviations() as $abbreviations) {
            foreach ($abbreviations as $abbreviation) {
                $timezones[$abbreviation['timezone_id'] ?? ''] = true;
            }
        }

        if (!isset($timezones[date_default_timezone_get()])) {
            $messages[] = 'mailvotech.install.timezone.not.supported';
        }

        if (!function_exists('json_encode')) {
            $messages[] = 'mailvotech.install.function.jsonencode';
        }

        if (!function_exists('session_start')) {
            $messages[] = 'mailvotech.install.function.sessionstart';
        }

        if (!function_exists('ctype_alpha')) {
            $messages[] = 'mailvotech.install.function.ctypealpha';
        }

        if (!function_exists('token_get_all')) {
            $messages[] = 'mailvotech.install.function.tokengetall';
        }

        if (!function_exists('simplexml_import_dom')) {
            $messages[] = 'mailvotech.install.function.simplexml';
        }

        if (false === $this->openSSLCipher->isSupported()) {
            $messages[] = 'mailvotech.install.extension.openssl';
        }

        if (!function_exists('curl_init')) {
            $messages[] = 'mailvotech.install.extension.curl';
        }

        if (!function_exists('finfo_open')) {
            $messages[] = 'mailvotech.install.extension.fileinfo';
        }

        if (!function_exists('mb_strtolower')) {
            $messages[] = 'mailvotech.install.extension.mbstring';
        }

        if (extension_loaded('xdebug')) {
            if (ini_get('xdebug.show_exception_trace')) {
                $messages[] = 'mailvotech.install.xdebug.exception.trace';
            }

            if (ini_get('xdebug.scream')) {
                $messages[] = 'mailvotech.install.xdebug.scream';
            }
        }

        return $messages;
    }

    public function checkOptionalSettings(): array
    {
        $messages = [];

        if (extension_loaded('xdebug')) {
            $cfgValue = ini_get('xdebug.max_nesting_level');

            if ($cfgValue <= 100) {
                $messages[] = 'mailvotech.install.xdebug.nesting';
            }
        }

        if (!extension_loaded('zip')) {
            $messages[] = 'mailvotech.install.extension.zip';
        }

        // We set a default timezone in the app bootstrap, but advise the user if their PHP config is missing it
        if (!ini_get('date.timezone')) {
            $messages[] = 'mailvotech.install.date.timezone.not.set';
        }

        if (!class_exists('\\DomDocument')) {
            $messages[] = 'mailvotech.install.module.phpxml';
        }

        if (!function_exists('iconv')) {
            $messages[] = 'mailvotech.install.function.iconv';
        }

        if (!extension_loaded('xml')) {
            $messages[] = 'mailvotech.install.function.xml';
        }

        if (!function_exists('imap_open')) {
            $messages[] = 'mailvotech.install.extension.imap';
        }

        if (!$this->site_url || !str_starts_with($this->site_url, 'https')) {
            $messages[] = 'mailvotech.install.ssl.certificate';
        }

        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            if (!function_exists('posix_isatty')) {
                $messages[] = 'mailvotech.install.function.posix.enable';
            }
        }

        $memoryLimit    = FileHelper::convertPHPSizeToBytes(ini_get('memory_limit'));
        $suggestedLimit = FileHelper::convertPHPSizeToBytes(self::RECOMMENDED_MEMORY_LIMIT);
        if ($memoryLimit > -1 && $memoryLimit < $suggestedLimit) {
            $messages[] = 'mailvotech.install.memory.limit';
        }

        if (!class_exists('\\Locale')) {
            $messages[] = 'mailvotech.install.module.intl';
        }

        if (class_exists('\\Collator')) {
            try {
                new \Collator('fr_FR');
            } catch (\Exception) {
                $messages[] = 'mailvotech.install.intl.config';
            }
        }

        if (-1 !== (int) ini_get('zend.assertions')) {
            $messages[] = 'mailvotech.install.zend_assertions';
        }

        return $messages;
    }

    public function getTemplate(): string
    {
        return '@MailVotechInstall/Install/check.html.twig';
    }

    /**
     * @return mixed[]
     */
    public function update(StepInterface $data): array
    {
        $parameters = [];

        foreach ($data as $key => $value) {
            // Exclude keys from the config
            if (!in_array($key, ['configIsWritable', 'projectDir'])) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }
}
