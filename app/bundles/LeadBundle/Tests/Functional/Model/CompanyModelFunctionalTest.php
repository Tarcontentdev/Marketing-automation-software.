<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Functional\Model;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\CoreBundle\Tests\Functional\CreateTestEntitiesTrait;
use MailVotech\LeadBundle\Entity\Company;
use MailVotech\LeadBundle\Entity\CompanyLead;
use MailVotech\LeadBundle\Entity\CompanyLeadRepository;
use MailVotech\LeadBundle\Model\CompanyModel;

final class CompanyModelFunctionalTest extends MailVotechMysqlTestCase
{
    use CreateTestEntitiesTrait;

    public function testAddLeadToCompanyWithLeadAsArray(): void
    {
        // Create a lead
        $lead = $this->createLead('User', 'One', 'user@company_a.com');
        // Create a company
        $company = $this->createCompany('Company A', 'contact@company_a.com');
        $this->em->flush();

        /** @var CompanyLeadRepository $companyLeadRepo */
        $companyLeadRepo = $this->em->getRepository(CompanyLead::class);

        $this->assertEquals(0, $companyLeadRepo->count([]));

        /** @var CompanyModel $companyModel */
        $companyModel = self::getContainer()->get(CompanyModel::class);
        $companyModel->addLeadToCompany($company, $lead->convertToArray());

        $this->assertEquals(1, $companyLeadRepo->count([]));
    }
}
