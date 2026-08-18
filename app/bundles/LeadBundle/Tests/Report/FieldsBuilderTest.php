<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Report;

use MailVotech\FormBundle\Entity\Field;
use MailVotech\LeadBundle\Model\FieldModel;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\LeadBundle\Model\ListModel;
use MailVotech\LeadBundle\Report\DncReportService;
use MailVotech\LeadBundle\Report\FieldsBuilder;
use MailVotech\UserBundle\Model\UserModel;

final class FieldsBuilderTest extends \PHPUnit\Framework\TestCase
{
    public function testGetLeadColumns(): void
    {
        $fieldModel = $this->createMock(FieldModel::class);

        $listModel = $this->createStub(ListModel::class);

        $userModel = $this->createStub(UserModel::class);

        $leadModel = $this->createStub(LeadModel::class);

        $fieldModel->expects($this->exactly(2)) // We have 2 asserts
            ->method('getLeadFields')
            ->with()
            ->willReturn($this->getFields());

        $dncReportService = $this->createStub(DncReportService::class);

        $fieldsBuilder = new FieldsBuilder($fieldModel, $listModel, $userModel, $leadModel, $dncReportService);

        $expected = [
            'l.id' => [
                'label' => 'mailvotech.lead.report.contact_id',
                'type'  => 'int',
                'link'  => 'mailvotech_contact_action',
            ],
            'i.ip_address' => [
                'label' => 'mailvotech.core.ipaddress',
                'type'  => 'text',
            ],
            'l.date_identified' => [
                'label'          => 'mailvotech.lead.report.date_identified',
                'type'           => 'datetime',
                'groupByFormula' => 'DATE(l.date_identified)',
            ],
            'l.date_added' => [
                'label'          => 'mailvotech.core.date.added',
                'type'           => 'datetime',
                'groupByFormula' => 'DATE(l.date_added)',
            ],
            'l.points' => [
                'label' => 'mailvotech.lead.points',
                'type'  => 'int',
            ],
            'l.owner_id' => [
                'label' => 'mailvotech.lead.report.owner_id',
                'type'  => 'int',
            ],
            'u.first_name' => [
                'label' => 'mailvotech.lead.report.owner_firstname',
                'type'  => 'string',
            ],
            'u.last_name' => [
                'label' => 'mailvotech.lead.report.owner_lastname',
                'type'  => 'string',
            ],
            'l.generated_email_domain' => [
                'label' => 'mailvotech.lead.report.generated_email_domain',
                'type'  => 'string',
            ],
            'x.title' => [
                'label' => 'Title',
                'type'  => 'string',
            ],
            'x.email' => [
                'label' => 'Email',
                'type'  => 'email',
            ],
            'x.mobile' => [
                'label' => 'Mobile',
                'type'  => 'string',
            ],
            'x.points' => [
                'label' => 'Points',
                'type'  => 'float',
            ],
            'x.date' => [
                'label' => 'Date',
                'type'  => 'date',
            ],
            'x.web' => [
                'label' => 'Website',
                'type'  => 'url',
            ],
        ];

        $columns = $fieldsBuilder->getLeadFieldsColumns('x');
        $this->assertSame($expected, $columns);

        $columns = $fieldsBuilder->getLeadFieldsColumns('x.');
        $this->assertSame($expected, $columns);
    }

    public function testGetLeadFilter(): void
    {
        $fieldModel = $this->createMock(FieldModel::class);

        $listModel = $this->createMock(ListModel::class);

        $userModel = $this->createMock(UserModel::class);

        $fieldModel->expects($this->once())
        ->method('getLeadFields')
            ->with()
            ->willReturn($this->getFields());

        $userSegments = [
            [
                'id'    => 1,
                'name'  => 'United States',
                'alias' => 'us',
            ],
            [
                'id'    => 2,
                'name'  => 'Segment with 3 filters',
                'alias' => 'segment-with-3-filters',
            ],
            [
                'id'    => 3,
                'name'  => 'Segment with 3 filters',
                'alias' => 'segment-with-3-filters1',
            ],
        ];

        $listModel->expects($this->once())
            ->method('getUserLists')
            ->with()
            ->willReturn($userSegments);

        $users = [
            0 => ['id' => 1, 'firstName' => 'John', 'lastName' => 'Doe'],
            1 => ['id' => 2, 'firstName' => 'Joe', 'lastName' => 'Smith'],
        ];

        $userModel->expects($this->once())
            ->method('getUserList')
            ->with()
            ->willReturn($users);

        $tagList = [
            [
                'value' => '1',
                'label' => 'A',
            ],
            [
                'value' => '2',
                'label' => 'B',
            ],
            [
                'value' => '3',
                'label' => 'C',
            ],
        ];
        $leadModel = $this->createMock(LeadModel::class);
        $leadModel->method('getTagList')
            ->with()
            ->willReturn($tagList);

        $dncReportService = $this->createStub(DncReportService::class);

        $fieldsBuilder = new FieldsBuilder($fieldModel, $listModel, $userModel, $leadModel, $dncReportService);

        $expected = [
            'l.id' => [
                'label' => 'mailvotech.lead.report.contact_id',
                'type'  => 'int',
                'link'  => 'mailvotech_contact_action',
            ],
            'i.ip_address' => [
                'label' => 'mailvotech.core.ipaddress',
                'type'  => 'text',
            ],
            'l.date_identified' => [
                'label'          => 'mailvotech.lead.report.date_identified',
                'type'           => 'datetime',
                'groupByFormula' => 'DATE(l.date_identified)',
            ],
            'l.date_added' => [
                'label'          => 'mailvotech.core.date.added',
                'type'           => 'datetime',
                'groupByFormula' => 'DATE(l.date_added)',
            ],
            'l.points' => [
                'label' => 'mailvotech.lead.points',
                'type'  => 'int',
            ],
            'l.owner_id' => [
                'label' => 'mailvotech.lead.report.owner_id',
                'type'  => 'int',
            ],
            'u.first_name' => [
                'label' => 'mailvotech.lead.report.owner_firstname',
                'type'  => 'string',
            ],
            'u.last_name' => [
                'label' => 'mailvotech.lead.report.owner_lastname',
                'type'  => 'string',
            ],
            'l.generated_email_domain' => [
                'label' => 'mailvotech.lead.report.generated_email_domain',
                'type'  => 'string',
            ],
            'x.title' => [
                'label' => 'Title',
                'type'  => 'string',
            ],
            'x.email' => [
                'label' => 'Email',
                'type'  => 'email',
            ],
            'x.mobile' => [
                'label' => 'Mobile',
                'type'  => 'string',
            ],
            'x.points' => [
                'label' => 'Points',
                'type'  => 'float',
            ],
            'x.date' => [
                'label' => 'Date',
                'type'  => 'date',
            ],
            'x.web' => [
                'label' => 'Website',
                'type'  => 'url',
            ],
            'segment.leadlist_id' => [
                'alias' => 'segment_id',
                'label' => 'mailvotech.core.filter.lists',
                'type'  => 'select',
                'list'  => [
                    1 => 'United States',
                    2 => 'Segment with 3 filters',
                    3 => 'Segment with 3 filters',
                ],
                'operators' => [
                    'eq' => 'mailvotech.core.operator.equals',
                ],
            ],
            'tag' => [
                'label' => 'mailvotech.core.filter.tags',
                'type'  => 'multiselect',
                'list'  => [
                    1 => 'A',
                    2 => 'B',
                    3 => 'C',
                ],
                'operators' => [
                    'in'       => 'mailvotech.core.operator.in',
                    'notIn'    => 'mailvotech.core.operator.notin',
                    'empty'    => 'mailvotech.core.operator.isempty',
                    'notEmpty' => 'mailvotech.core.operator.isnotempty',
                ],
            ],
            'x.owner_id' => [
                'label' => 'mailvotech.lead.list.filter.owner',
                'type'  => 'select',
                'list'  => [
                    1 => 'John Doe',
                    2 => 'Joe Smith',
                ],
            ],
        ];

        $columns = $fieldsBuilder->getLeadFilter('x', 'segment');
        $this->assertSame($expected, $columns);
    }

    public function testGetCompanyColumns(): void
    {
        $fieldModel = $this->createMock(FieldModel::class);

        $listModel = $this->createStub(ListModel::class);

        $userModel = $this->createStub(UserModel::class);

        $fieldModel->expects($this->exactly(2)) // We have 2 asserts
        ->method('getCompanyFields')
            ->with()
            ->willReturn($this->getFields());

        $leadModel = $this->createStub(LeadModel::class);

        $dncReportService = $this->createStub(DncReportService::class);

        $fieldsBuilder = new FieldsBuilder($fieldModel, $listModel, $userModel, $leadModel, $dncReportService);

        $expected = [
            'comp.id' => [
                'label' => 'mailvotech.lead.report.company.company_id',
                'type'  => 'int',
                'link'  => 'mailvotech_company_action',
            ],
            'comp.companyname' => [
                'label' => 'mailvotech.lead.report.company.company_name',
                'type'  => 'string',
                'link'  => 'mailvotech_company_action',
            ],
            'comp.companycity' => [
                'label' => 'mailvotech.lead.report.company.company_city',
                'type'  => 'string',
                'link'  => 'mailvotech_company_action',
            ],
            'comp.companystate' => [
                'label' => 'mailvotech.lead.report.company.company_state',
                'type'  => 'string',
                'link'  => 'mailvotech_company_action',
            ],
            'comp.companycountry' => [
                'label' => 'mailvotech.lead.report.company.company_country',
                'type'  => 'string',
                'link'  => 'mailvotech_company_action',
            ],
            'comp.companyindustry' => [
                'label' => 'mailvotech.lead.report.company.company_industry',
                'type'  => 'string',
                'link'  => 'mailvotech_company_action',
            ],
            'x.title' => [
                'label' => 'Title',
                'type'  => 'string',
            ],
            'x.email' => [
                'label' => 'Email',
                'type'  => 'email',
            ],
            'x.mobile' => [
                'label' => 'Mobile',
                'type'  => 'string',
            ],
            'x.points' => [
                'label' => 'Points',
                'type'  => 'float',
            ],
            'x.date' => [
                'label' => 'Date',
                'type'  => 'date',
            ],
            'x.web' => [
                'label' => 'Website',
                'type'  => 'url',
            ],
        ];

        $columns = $fieldsBuilder->getCompanyFieldsColumns('x');
        $this->assertSame($expected, $columns);

        $columns = $fieldsBuilder->getCompanyFieldsColumns('x.');
        $this->assertSame($expected, $columns);
    }

    /**
     * @return array<int, Field>
     */
    private function getFields(): array
    {
        $titleField = new Field();
        $titleField->setLabel('Title');
        $titleField->setAlias('title');
        $titleField->setType('string');

        $emailField = new Field();
        $emailField->setLabel('Email');
        $emailField->setAlias('email');
        $emailField->setType('email');

        $mobileField = new Field();
        $mobileField->setLabel('Mobile');
        $mobileField->setAlias('mobile');
        $mobileField->setType('tel');

        $pointField = new Field();
        $pointField->setLabel('Points');
        $pointField->setAlias('points');
        $pointField->setType('number');

        $dateField = new Field();
        $dateField->setLabel('Date');
        $dateField->setAlias('date');
        $dateField->setType('date');

        $webField = new Field();
        $webField->setLabel('Website');
        $webField->setAlias('web');
        $webField->setType('url');

        return [
            $titleField,
            $emailField,
            $mobileField,
            $pointField,
            $dateField,
            $webField,
        ];
    }
}
