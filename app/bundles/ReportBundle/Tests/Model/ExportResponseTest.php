<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\Tests\Model;

use MailVotech\ReportBundle\Model\ExportResponse;
use Symfony\Component\HttpFoundation\Response;

final class ExportResponseTest extends \PHPUnit\Framework\TestCase
{
    public function testResponse(): void
    {
        $responce = new Response();
        ExportResponse::setResponseHeaders($responce, 'fileName.csv');

        $this->assertSame('application/octet-stream', $responce->headers->get('Content-Type'));
        $this->assertSame('attachment; filename="fileName.csv"', $responce->headers->get('Content-Disposition'));
        $this->assertSame('must-revalidate, private', $responce->headers->get('Cache-Control'));
        $this->assertSame('public', $responce->headers->get('Pragma'));
    }
}
