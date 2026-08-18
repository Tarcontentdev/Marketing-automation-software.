<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechCrmBundle\Tests;

use MailVotech\EmailBundle\Helper\EmailValidator;
use MailVotech\LeadBundle\Deduplicate\CompanyDeduper;
use MailVotech\LeadBundle\Entity\Company;
use MailVotech\PluginBundle\Tests\Integration\AbstractIntegrationTestCase;
use MailVotechPlugin\MailVotechCrmBundle\Integration\VtigerIntegration;
use MailVotechPlugin\MailVotechCrmBundle\Tests\Fixtures\Model\CompanyModelStub;

final class CrmAbstractIntegrationTest extends AbstractIntegrationTestCase
{
    public function testFieldMatchingPriority(): void
    {
        $config = [
            'update_mailvotech' => [
                'email'      => '1',
                'first_name' => '0',
                'last_name'  => '0',
                'address_1'  => '1',
                'address_2'  => '1',
            ],
        ];

        $mockBuilder = $this->getMockBuilder(VtigerIntegration::class);
        $mockBuilder->disableOriginalConstructor();

        /** @var VtigerIntegration $integration */
        $integration = $mockBuilder->getMock();

        $methodMailVotech = new \ReflectionMethod(VtigerIntegration::class, 'getPriorityFieldsForMailVotech');

        $methodIntegration = new \ReflectionMethod(VtigerIntegration::class, 'getPriorityFieldsForIntegration');

        $fieldsForMailVotech = $methodMailVotech->invokeArgs($integration, [$config]);

        $this->assertSame(
            ['email', 'address_1', 'address_2'],
            $fieldsForMailVotech,
            'Fields to update in MailVotech should return fields marked as 1 in the integration priority config.'
        );

        $fieldsForIntegration = $methodIntegration->invokeArgs($integration, [$config]);

        $this->assertSame(
            ['first_name', 'last_name'],
            $fieldsForIntegration,
            'Fields to update in the integration should return fields marked as 0 in the integration priority config.'
        );
    }

    public function testCompanyDataIsMappedForNewCompanies(): void
    {
        $data = [
            'custom_company_name' => 'Some Business',
            'some_custom_field'   => 'some value',
        ];

        $emailValidator = $this->createStub(EmailValidator::class);

        $companyDeduper = $this->createStub(CompanyDeduper::class);

        $companyModel = $this->getMockBuilder(CompanyModelStub::class)
            ->onlyMethods(['fetchCompanyFields', 'organizeFieldsByGroup', 'saveEntity'])
            ->disableOriginalConstructor()
            ->getMock();
        $companyModel->setFieldModel($this->fieldModel);
        $companyModel->setEmailValidator($emailValidator);
        $companyModel->setCompanyDeduper($companyDeduper);

        $companyModel
            ->method('fetchCompanyFields')
            ->willReturn([]);
        $companyModel->expects($this->once())
            ->method('organizeFieldsByGroup')
            ->willReturn([
                'core' => [
                    'companyname' => [
                        'alias' => 'companyname',
                        'type'  => 'text',
                    ],
                    'custom_company_name' => [
                        'alias' => 'custom_company_name',
                        'type'  => 'text',
                    ],
                    'some_custom_field' => [
                        'alias' => 'some_custom_field',
                        'type'  => 'text',
                    ],
                ],
            ]);

        $integration = $this->getMockBuilder(VtigerIntegration::class)
            ->setConstructorArgs([
                $this->dispatcher,
                $this->cache,
                $this->em,
                $this->request,
                $this->router,
                $this->translator,
                $this->logger,
                $this->encryptionHelper,
                $this->leadModel,
                $companyModel,
                $this->pathsHelper,
                $this->notificationModel,
                $this->fieldModel,
                $this->integrationEntityModel,
                $this->doNotContact,
                $this->fieldsWithUniqueIdentifier,
            ])
            ->onlyMethods(['populateMailVotechLeadData', 'mergeConfigToFeatureSettings'])
            ->getMock();

        $integration->expects($this->once())
            ->method('populateMailVotechLeadData')
            ->willReturn($data);

        $company = $integration->getMailVotechCompany($data);
        $this->assertInstanceOf(Company::class, $company);

        $this->assertEquals('Some Business', $company->getName());
        $this->assertEquals('Some Business', $company->getFieldValue('custom_company_name'));
        $this->assertEquals('some value', $company->getFieldValue('some_custom_field'));
    }

    public function testLimitString(): void
    {
        $integration = $this->createStub(VtigerIntegration::class);

        $methodLimitString = new \ReflectionMethod(VtigerIntegration::class, 'limitString');

        $string = 'SomeRandomString';

        $result = $methodLimitString->invokeArgs($integration, [str_repeat($string, 100), 'text']);
        $this->assertSame(255, strlen($result));

        $result = $methodLimitString->invokeArgs($integration, [$string, 'text']);
        $this->assertSame(strlen($result), strlen($string));
        $this->assertSame($result, $string);

        $result = $methodLimitString->invokeArgs($integration, [true, 'text']);
        $this->assertTrue($result);

        $result = $methodLimitString->invokeArgs($integration, [false, 'text']);
        $this->assertFalse($result);

        $result = $methodLimitString->invokeArgs($integration, [[1, 2, 3]]);
        $this->assertSame($result, [1, 2, 3]);
    }
}
