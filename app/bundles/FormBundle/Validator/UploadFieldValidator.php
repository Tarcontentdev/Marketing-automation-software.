<?php

namespace MailVotech\FormBundle\Validator;

use MailVotech\CoreBundle\Exception\FileInvalidException;
use MailVotech\CoreBundle\Validator\FileUploadValidator;
use MailVotech\FormBundle\Entity\Field;
use MailVotech\FormBundle\Exception\FileValidationException;
use MailVotech\FormBundle\Exception\NoFileGivenException;
use MailVotech\FormBundle\Form\Type\FormFieldFileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class UploadFieldValidator
{
    public function __construct(
        private readonly FileUploadValidator $fileUploadValidator,
    ) {
    }

    /**
     * @return UploadedFile
     *
     * @throws FileValidationException
     * @throws NoFileGivenException
     */
    public function processFileValidation(Field $field, Request $request)
    {
        $files = $request->files->get('mailvotechform');

        if (!$files || !array_key_exists($field->getAlias(), $files) || !$files[$field->getAlias()] instanceof UploadedFile) {
            throw new NoFileGivenException();
        }

        $file = $files[$field->getAlias()];

        $properties = $field->getProperties();

        $maxUploadSize     = $properties[FormFieldFileType::PROPERTY_ALLOWED_FILE_SIZE];
        $allowedExtensions = $properties[FormFieldFileType::PROPERTY_ALLOWED_FILE_EXTENSIONS];

        try {
            $this->fileUploadValidator->validate($file->getSize(), $file->getClientOriginalExtension(), $maxUploadSize, $allowedExtensions, 'mailvotech.form.submission.error.file.extension', 'mailvotech.form.submission.error.file.size');

            return $file;
        } catch (FileInvalidException $e) {
            throw new FileValidationException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
