<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\Tests\Helper;

use MailVotech\ReportBundle\Helper\ReportHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ReportHelperTest extends TestCase
{
    private ReportHelper $reportHelper;

    protected function setUp(): void
    {
        $this->reportHelper = new ReportHelper($this->createStub(EventDispatcherInterface::class));
    }

    public function testGetStandardColumnsMethodReturnsCorrectColumns(): void
    {
        $columns = $this->reportHelper->getStandardColumns('somePrefix');

        $expectedColumnns = [
            'somePrefixid' => [
                'label' => 'mailvotech.core.id',
                'type'  => 'int',
                'alias' => 'somePrefixid',
            ],
            'somePrefixname' => [
                'label' => 'mailvotech.core.name',
                'type'  => 'string',
                'alias' => 'somePrefixname',
            ],
            'somePrefixcreated_by_user' => [
                'label' => 'mailvotech.core.createdby',
                'type'  => 'string',
                'alias' => 'somePrefixcreated_by_user',
            ],
            'somePrefixdate_added' => [
                'label' => 'mailvotech.report.field.date_added',
                'type'  => 'datetime',
                'alias' => 'somePrefixdate_added',
            ],
            'somePrefixmodified_by_user' => [
                'label' => 'mailvotech.report.field.modified_by_user',
                'type'  => 'string',
                'alias' => 'somePrefixmodified_by_user',
            ],
            'somePrefixdate_modified' => [
                'label' => 'mailvotech.report.field.date_modified',
                'type'  => 'datetime',
                'alias' => 'somePrefixdate_modified',
            ],
            'somePrefixdescription' => [
                'label' => 'mailvotech.core.description',
                'type'  => 'string',
                'alias' => 'somePrefixdescription',
            ],
            'somePrefixpublish_up' => [
                'label' => 'mailvotech.report.field.publish_up',
                'type'  => 'datetime',
                'alias' => 'somePrefixpublish_up',
            ],
            'somePrefixpublish_down' => [
                'label' => 'mailvotech.report.field.publish_down',
                'type'  => 'datetime',
                'alias' => 'somePrefixpublish_down',
            ],
            'somePrefixis_published' => [
                'label' => 'mailvotech.report.field.is_published',
                'type'  => 'bool',
                'alias' => 'somePrefixis_published',
            ],
        ];

        $this->assertSame($expectedColumnns, $columns);
    }
}
