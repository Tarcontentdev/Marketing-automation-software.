<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Command;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\LeadBundle\Command\DeleteContactSecondaryCompaniesCommand;
use MailVotech\LeadBundle\Entity\Company;
use MailVotech\LeadBundle\Entity\CompanyLead;
use MailVotech\LeadBundle\Entity\CompanyLeadRepository;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Model\LeadModel;

final class DeleteContactSecondaryCompaniesCommandTest extends MailVotechMysqlTestCase
{
    protected $useCleanupRollback = false;

    public function testDeleteContactSecondaryCompanies(): void
    {
        $contact          = $this->getContactWithCompanies();
        /** @var CompanyLeadRepository $companyLeadRepo */
        $companyLeadRepo  = $this->em->getRepository(CompanyLead::class);

        $contactCompanies = $companyLeadRepo->getCompaniesByLeadId($contact->getId());
        $this->assertCount(2, $contactCompanies);

        $this->testSymfonyCommand(DeleteContactSecondaryCompaniesCommand::NAME);

        $contactCompanies = $companyLeadRepo->getCompaniesByLeadId($contact->getId());
        $this->assertCount(2, $contactCompanies);

        $this->setUpSymfony(['contact_allow_multiple_companies' => 0]);
        $this->testSymfonyCommand(DeleteContactSecondaryCompaniesCommand::NAME);

        $contactCompanies = $companyLeadRepo->getCompaniesByLeadId($contact->getId());
        $this->assertCount(1, $contactCompanies);
    }

    /**
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function getContactWithCompanies(): Lead
    {
        $company = new Company();
        $company->setName('Doe Corp');

        $this->em->persist($company);

        $company2 = new Company();
        $company2->setName('Doe Corp 2');

        $this->em->persist($company2);

        $contact = new Lead();
        $contact->setEmail('test@test.com');

        $this->em->persist($contact);
        $this->em->flush();

        /** @var LeadModel $leadModel */
        $leadModel = self::getContainer()->get(LeadModel::class);
        $this->assertTrue($leadModel->addToCompany($contact, $company));
        $this->assertTrue($leadModel->addToCompany($contact, $company2));

        return $contact;
    }
}
