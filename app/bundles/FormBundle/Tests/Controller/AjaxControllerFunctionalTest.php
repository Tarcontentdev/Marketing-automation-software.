<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Tests\Controller;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\Request;

final class AjaxControllerFunctionalTest extends MailVotechMysqlTestCase
{
    public function testGetFieldsForObjectAction(): void
    {
        $this->client->xmlHttpRequest(
            Request::METHOD_GET,
            '/s/ajax?action=form:getFieldsForObject&mappedObject=company&mappedField=&formId=10'
        );
        $clientResponse = $this->client->getResponse();
        $payload        = json_decode($clientResponse->getContent(), true);
        self::assertResponseIsSuccessful();

        // Assert some random fields exist.
        $this->assertSame([
            'label'      => 'Company Email',
            'value'      => 'companyemail',
            'isListType' => false,
        ], $payload['fields'][4]);
        $this->assertSame([
            'label'      => 'Industry',
            'value'      => 'companyindustry',
            'isListType' => true,
        ], $payload['fields'][9]);
    }
}
