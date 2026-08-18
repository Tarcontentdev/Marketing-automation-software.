<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Deduplicate;

use MailVotech\LeadBundle\Deduplicate\CompanyDeduper;
use MailVotech\LeadBundle\Entity\CompanyRepository;
use MailVotech\LeadBundle\Exception\UniqueFieldNotFoundException;
use MailVotech\LeadBundle\Field\FieldsWithUniqueIdentifier;
use MailVotech\LeadBundle\Model\FieldModel;
use PHPUnit\Framework\MockObject\MockObject;

final class CompanyDeduperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject&FieldModel
     */
    private MockObject $fieldModel;

    protected function setUp(): void
    {
        $this->fieldModel = $this->createMock(FieldModel::class);
    }

    public function testUniqueFieldNotFoundException(): void
    {
        $this->expectException(UniqueFieldNotFoundException::class);
        $this->fieldModel->method('getFieldList')->willReturn([]);
        $this->getDeduper()->checkForDuplicateCompanies([]);
    }

    private function getDeduper(): CompanyDeduper
    {
        return new CompanyDeduper(
            $this->fieldModel,
            $this->createStub(FieldsWithUniqueIdentifier::class),
            $this->createStub(CompanyRepository::class)
        );
    }
}
