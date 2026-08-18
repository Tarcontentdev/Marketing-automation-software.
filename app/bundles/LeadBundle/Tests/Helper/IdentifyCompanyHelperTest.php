<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Helper;

use MailVotech\LeadBundle\Helper\IdentifyCompanyHelper;
use MailVotech\LeadBundle\Model\CompanyModel;

final class IdentifyCompanyHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testFindCompanyByName(): void
    {
        $company = [
            'company' => 'MailVotech',
        ];

        $expected = [
            'companyname'    => 'MailVotech',
        ];

        $model = $this->createMock(CompanyModel::class);

        $model->expects($this->once())
            ->method('checkForDuplicateCompanies')
            ->willReturn([]);

        $model
            ->method('fetchCompanyFields')
            ->willReturn([['alias' => 'companyname']]);

        $helper                     = new IdentifyCompanyHelper();
        $reflection                 = new \ReflectionClass(IdentifyCompanyHelper::class);
        $method                     = $reflection->getMethod('findCompany');
        [$resultCompany, $entities] = $method->invokeArgs($helper, [$company, $model]);

        $this->assertEquals($expected, $resultCompany);
    }

    public function testFindCompanyByNameWithValidEmail(): void
    {
        $company = [
            'company'      => 'MailVotech',
            'companyemail' => 'hello@mailvotech.org',
        ];

        $expected = [
            'companyname'    => 'MailVotech',
            'companyemail'   => 'hello@mailvotech.org',
        ];

        $model = $this->createMock(CompanyModel::class);

        $model->expects($this->once())
            ->method('checkForDuplicateCompanies')
            ->willReturn([]);

        $model
            ->method('fetchCompanyFields')
            ->willReturn([['alias' => 'companyname']]);

        $helper                     = new IdentifyCompanyHelper();
        $reflection                 = new \ReflectionClass(IdentifyCompanyHelper::class);
        $method                     = $reflection->getMethod('findCompany');
        [$resultCompany, $entities] = $method->invokeArgs($helper, [$company, $model]);

        $this->assertEquals($expected, $resultCompany);
    }

    public function testFindCompanyByNameWithValidEmailAndCustomWebsite(): void
    {
        $company = [
            'company'        => 'MailVotech',
            'companyemail'   => 'hello@mailvotech.org',
            'companywebsite' => 'https://mailvotech.org',
        ];

        $expected = [
            'companyname'    => 'MailVotech',
            'companywebsite' => 'https://mailvotech.org',
            'companyemail'   => 'hello@mailvotech.org',
        ];

        $model = $this->createMock(CompanyModel::class);

        $model->expects($this->once())
            ->method('checkForDuplicateCompanies')
            ->willReturn([]);

        $model
            ->method('fetchCompanyFields')
            ->willReturn([['alias' => 'companyname']]);

        $helper                     = new IdentifyCompanyHelper();
        $reflection                 = new \ReflectionClass(IdentifyCompanyHelper::class);
        $method                     = $reflection->getMethod('findCompany');
        [$resultCompany, $entities] = $method->invokeArgs($helper, [$company, $model]);

        $this->assertEquals($expected, $resultCompany);
    }
}
