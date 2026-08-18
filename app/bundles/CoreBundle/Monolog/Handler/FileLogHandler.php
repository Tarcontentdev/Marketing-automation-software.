<?php

namespace MailVotech\CoreBundle\Monolog\Handler;

use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;

final class FileLogHandler extends RotatingFileHandler
{
    public function __construct(CoreParametersHelper $coreParametersHelper, FormatterInterface $exceptionFormatter)
    {
        $logPath     = $coreParametersHelper->get('log_path');
        $logFileName = $coreParametersHelper->get('log_file_name');
        $maxFiles    = $coreParametersHelper->get('max_log_files');
        $debugMode   = $coreParametersHelper->get('debug', false) || (defined('MAILVOTECH_ENV') && 'dev' === MAILVOTECH_ENV);
        $level       = $debugMode ? Level::Debug : Level::Notice;

        if ($debugMode) {
            $this->setFormatter($exceptionFormatter);
        }

        parent::__construct(sprintf('%s/%s', $logPath, $logFileName), $maxFiles, $level);
    }
}
