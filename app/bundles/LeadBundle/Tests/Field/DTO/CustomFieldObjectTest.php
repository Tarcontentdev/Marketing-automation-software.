<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Field\DTO;

use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Exception\InvalidObjectTypeException;
use MailVotech\LeadBundle\Field\DTO\CustomFieldObject;

final class CustomFieldObjectTest extends \PHPUnit\Framework\TestCase
{
    public function testLeadObject(): void
    {
        $leadField = new LeadField();

        $customFieldObject = new CustomFieldObject($leadField);

        $this->assertSame('leads', $customFieldObject->getObject());
    }

    public function testCompanyObject(): void
    {
        $leadField = new LeadField();
        $leadField->setObject('company');

        $customFieldObject = new CustomFieldObject($leadField);

        $this->assertSame('companies', $customFieldObject->getObject());
    }

    public function testInvalidObject(): void
    {
        $leadField = new LeadField();
        $leadField->setObject('xxx');

        $this->expectException(InvalidObjectTypeException::class);
        $this->expectExceptionMessage('xxx has no associated object');

        new CustomFieldObject($leadField);
    }
}
