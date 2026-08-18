<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Command;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\LeadBundle\Command\UpdateCompanyNameOnLeadsCommand;
use MailVotech\LeadBundle\Entity\Company;
use MailVotech\LeadBundle\Entity\CompanyRepository;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadRepository;
use MailVotech\LeadBundle\Tests\TestEntityCreationTrait;

final class UpdateCompanyNameOnLeadsCommandFunctionalTest extends MailVotechMysqlTestCase
{
    use TestEntityCreationTrait;

    protected $useCleanupRollback = false;

    public function testUpdateCompanies(): void
    {
        /** @var CompanyRepository $companyRepository */
        $companyRepository = $this->em->getRepository(Company::class);

        /** @var LeadRepository $contactRepository */
        $contactRepository = $this->em->getRepository(Lead::class);

        $contact1 = $this->createContact();
        $contact2 = $this->createContact();
        $contact3 = $this->createContact();
        $contact4 = $this->createContact();

        $company1 = $this->createCompany();
        $company2 = $this->createCompany();
        $company3 = $this->createCompany();

        $this->attachContactToCompany($contact1, $company1, true);
        $this->attachContactToCompany($contact1, $company2);
        $this->attachContactToCompany($contact2, $company1, true);
        $this->attachContactToCompany($contact3, $company1);

        $this->attachContactToCompany($contact3, $company2, true);

        $this->attachContactToCompany($contact4, $company2, true);

        $this->attachContactToCompany($contact3, $company3);

        $this->updateCompanyName($company1);

        $this->testSymfonyCommand(UpdateCompanyNameOnLeadsCommand::COMMAND_NAME, ['--company-id' => $company1->getId()]);

        $this->em->clear();

        $this->assertSame($companyRepository->getEntity($company1->getId())->getName(), $contactRepository->getEntity($contact1->getId())->getCompany(), 'Company name is updated on leads.');
        $this->assertSame($companyRepository->getEntity($company1->getId())->getName(), $contactRepository->getEntity($contact2->getId())->getCompany(), 'Company name is updated on leads.');
        $this->assertSame($companyRepository->getEntity($company2->getId())->getName(), $contactRepository->getEntity($contact3->getId())->getCompany(), 'Company name is not updated and remains same as it\'s primary company');

        $this->updateCompanyName($companyRepository->getEntity($company2->getId()));

        $this->testSymfonyCommand(UpdateCompanyNameOnLeadsCommand::COMMAND_NAME);
        $this->em->clear();

        $this->assertSame($companyRepository->getEntity($company2->getId())->getName(), $contactRepository->getEntity($contact3->getId())->getCompany(), 'Company name is updated on leads.');
        $this->assertSame($companyRepository->getEntity($company2->getId())->getName(), $contactRepository->getEntity($contact4->getId())->getCompany(), 'Company name is updated on leads.');
        $this->assertSame($companyRepository->getEntity($company1->getId())->getName(), $contactRepository->getEntity($contact1->getId())->getCompany(), 'Company name is not updated and remains same as it\'s primary company');
    }
}
