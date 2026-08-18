<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Tests\Validator;

use MailVotech\CoreBundle\Exception\FileInvalidException;
use MailVotech\CoreBundle\Validator\FileUploadValidator;
use MailVotech\FormBundle\Entity\Field;
use MailVotech\FormBundle\Exception\FileValidationException;
use MailVotech\FormBundle\Exception\NoFileGivenException;
use MailVotech\FormBundle\Validator\UploadFieldValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(UploadFieldValidator::class)]
final class UploadFieldValidatorTest extends \PHPUnit\Framework\TestCase
{
    #[TestDox('No Files given')]
    public function testNoFilesGiven(): void
    {
        $fileUploadValidatorMock = $this->createMock(FileUploadValidator::class);

        $fileUploadValidatorMock->expects($this->never())
            ->method('validate');

        $parameterBagMock = $this->createMock(FileBag::class);

        $parameterBagMock->expects($this->once())
            ->method('get')
            ->with('mailvotechform')
            ->willReturn(false);

        $request        = new Request();
        $request->files = $parameterBagMock;

        $fileUploadValidator = new UploadFieldValidator($fileUploadValidatorMock);

        $field = new Field();

        $this->expectException(NoFileGivenException::class);

        $fileUploadValidator->processFileValidation($field, $request);
    }

    #[TestDox('Exception should be thrown when validation fails')]
    public function testValidationFailed(): void
    {
        $fileUploadValidatorMock = $this->createMock(FileUploadValidator::class);

        $fileUploadValidatorMock->expects($this->once())
            ->method('validate')
            ->willThrowException(new FileInvalidException('Validation failed'));

        $parameterBagMock = $this->createMock(FileBag::class);

        $fileMock = $this->createStub(UploadedFile::class);

        $files = [
            'file' => $fileMock,
        ];

        $parameterBagMock->expects($this->once())
            ->method('get')
            ->with('mailvotechform')
            ->willReturn($files);

        $request        = new Request();
        $request->files = $parameterBagMock;

        $fileUploadValidator = new UploadFieldValidator($fileUploadValidatorMock);

        $field = new Field();
        $field->setAlias('file');
        $field->setProperties([
            'allowed_file_size'       => 1,
            'allowed_file_extensions' => ['jpg', 'gif'],
        ]);

        $this->expectException(FileValidationException::class);
        $this->expectExceptionMessage('Validation failed');

        $fileUploadValidator->processFileValidation($field, $request);
    }

    #[TestDox('No validation error')]
    public function testFileIsValid(): void
    {
        $fileUploadValidatorMock = $this->createMock(FileUploadValidator::class);

        $fileUploadValidatorMock->expects($this->once())
            ->method('validate');

        $parameterBagMock = $this->createMock(FileBag::class);

        $fileMock = $this->createStub(UploadedFile::class);

        $files = [
            'file' => $fileMock,
        ];

        $parameterBagMock->expects($this->once())
            ->method('get')
            ->with('mailvotechform')
            ->willReturn($files);

        $request        = new Request();
        $request->files = $parameterBagMock;

        $fileUploadValidator = new UploadFieldValidator($fileUploadValidatorMock);

        $field = new Field();
        $field->setAlias('file');
        $field->setProperties([
            'allowed_file_size'       => 1,
            'allowed_file_extensions' => ['jpg', 'gif'],
        ]);

        $file = $fileUploadValidator->processFileValidation($field, $request);

        $this->assertSame($fileMock, $file);
    }
}
