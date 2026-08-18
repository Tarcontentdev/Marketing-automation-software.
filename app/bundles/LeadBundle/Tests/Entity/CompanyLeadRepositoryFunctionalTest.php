<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Entity;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\LeadBundle\Entity\Company;
use MailVotech\LeadBundle\Entity\CompanyLead;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Model\CompanyModel;

final class CompanyLeadRepositoryFunctionalTest extends MailVotechMysqlTestCase
{
    public function testGetCompaniesByLeadIds(): void
    {
        $companyA = $this->createCompany('Company A');
        $companyB = $this->createCompany('Company B');
        $companyC = $this->createCompany('Company C');
        $leadA    = $this->createLead('Company A');
        $leadB    = $this->createLead('Company B');
        $leadC    = $this->createLead('Company C');
        $leadD    = $this->createLead('Company D');

        $this->createCompanyLeadRelation($companyA, $leadA, true);
        $this->createCompanyLeadRelation($companyA, $leadD);
        $this->createCompanyLeadRelation($companyB, $leadB, true);
        $this->createCompanyLeadRelation($companyB, $leadA);
        $this->createCompanyLeadRelation($companyC, $leadC, true);
        $this->createCompanyLeadRelation($companyC, $leadA);
        $this->createCompanyLeadRelation($companyC, $leadB);

        $this->em->flush();

        /** @var CompanyModel $companyModel */
        $companyModel    = self::getContainer()->get(CompanyModel::class);
        $repoCompanyLead = $companyModel->getCompanyLeadRepository();

        $this->assertCount(0, $repoCompanyLead->getPrimaryCompaniesByLeadIds([]), 'No IDs');
        $this->assertCount(0, $repoCompanyLead->getPrimaryCompaniesByLeadIds([0]), 'Empty IDs');
        $this->assertCount(2, $repoCompanyLead->getPrimaryCompaniesByLeadIds([$leadA->getId(), $leadB->getId(), $leadD->getId()]), 'Primary Company Lead relations for LeadA and LeadD');
    }

    public function createCompany(string $name): Company
    {
        $company = new Company();
        $company->setName($name);
        $this->em->persist($company);

        return $company;
    }

    private function createLead(string $companyName): Lead
    {
        $lead = new Lead();
        $lead->setCompany($companyName);
        $this->em->persist($lead);

        return $lead;
    }

    private function createCompanyLeadRelation(Company $company, Lead $lead, bool $isPrimary = false): void
    {
        $companyLead = new CompanyLead();
        $companyLead->setCompany($company);
        $companyLead->setLead($lead);
        $companyLead->setDateAdded(new \DateTime());
        $companyLead->setPrimary($isPrimary);

        $this->em->persist($companyLead);
    }
}
