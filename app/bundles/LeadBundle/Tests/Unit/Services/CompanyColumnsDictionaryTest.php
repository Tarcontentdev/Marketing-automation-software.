<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Unit\Services;

use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\LeadBundle\Field\FieldList;
use MailVotech\LeadBundle\Services\CompanyColumnsDictionary;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CompanyColumnsDictionaryTest extends TestCase
{
    private CoreParametersHelper&MockObject $coreParametersHelper;

    private CompanyColumnsDictionary $dictionary;

    protected function setUp(): void
    {
        parent::setUp();

        $fieldList                  = $this->createMock(FieldList::class);
        $translator                 = $this->createMock(TranslatorInterface::class);
        $this->coreParametersHelper = $this->createMock(CoreParametersHelper::class);

        $translator->method('trans')->willReturnArgument(0);

        $fieldList->expects($this->once())
            ->method('getFieldList')
            ->with(false, true, ['isPublished' => true, 'object' => 'company'])
            ->willReturn(['annual_revenue' => 'Annual Revenue']);

        $this->dictionary = new CompanyColumnsDictionary(
            $fieldList,
            $translator,
            $this->coreParametersHelper,
        );
    }

    public function testGetColumnsResolvesLabelsAndOrder(): void
    {
        $this->coreParametersHelper->expects($this->once())
            ->method('get')
            ->with('company_columns', [])
            ->willReturn([
                'companywebsite',
                'companyname',
            ]);

        $columns = $this->dictionary->getColumns();

        $this->assertSame(['companywebsite' => 'mailvotech.company.website', 'companyname' => 'mailvotech.company.name'], $columns);
    }

    public function testGetFieldsMergesCoreAndCompanyCustomFields(): void
    {
        $fields = $this->dictionary->getFields();

        $this->assertArrayHasKey('companyname', $fields);
        $this->assertArrayHasKey('leadcount', $fields);
        $this->assertArrayHasKey('annual_revenue', $fields);
        $this->assertSame('Annual Revenue', $fields['annual_revenue']);
    }
}
