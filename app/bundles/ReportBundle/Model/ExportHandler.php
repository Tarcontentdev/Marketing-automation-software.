<?php

namespace MailVotech\ReportBundle\Model;

use MailVotech\CoreBundle\Exception\FilePathException;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\FilePathResolver;
use MailVotech\ReportBundle\Exception\FileIOException;

class ExportHandler
{
    /**
     * @var string
     */
    private $dir;

    public function __construct(
        CoreParametersHelper $coreParametersHelper,
        private readonly FilePathResolver $filePathResolver,
    ) {
        $this->dir              = $coreParametersHelper->get('report_temp_dir');
    }

    /**
     * @return bool|resource
     *
     * @throws FileIOException
     */
    public function getHandler($fileName)
    {
        $path = $this->getPath($fileName);

        if (false === ($handler = @fopen($path, 'a'))) {
            throw new FileIOException('Could not open file '.$path);
        }

        return $handler;
    }

    /**
     * @param resource $handler
     */
    public function closeHandler($handler): void
    {
        fclose($handler);
    }

    /**
     * @param string $fileName
     */
    public function removeFile($fileName): void
    {
        try {
            $path = $this->getPath($fileName);
            $this->filePathResolver->delete($path);
        } catch (FileIOException) {
        }
    }

    /**
     * @throws FileIOException
     */
    public function getPath($fileName): string
    {
        try {
            $this->filePathResolver->createDirectory($this->dir);
        } catch (FilePathException $e) {
            throw new FileIOException('Could not create directory '.$this->dir, 0, $e);
        }

        return $this->dir.'/'.$fileName.'.csv';
    }
}
