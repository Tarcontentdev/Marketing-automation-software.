<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Command;

use MailVotech\CoreBundle\Helper\ExitCode;
use MailVotech\LeadBundle\Entity\Company;
use MailVotech\LeadBundle\Entity\CompanyLeadRepository;
use MailVotech\LeadBundle\Entity\CompanyRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: UpdateCompanyNameOnLeadsCommand::COMMAND_NAME,
    description: 'Update company name in leads table.'
)]
final readonly class UpdateCompanyNameOnLeadsCommand
{
    public const COMMAND_NAME = 'mailvotech:company:update_lead_company';

    public function __construct(
        private CompanyLeadRepository $companyLeadRepository,
        private CompanyRepository $companyRepository,
    ) {
    }

    public function __invoke(
        OutputInterface $output,
        #[Option(description: 'Company id to update.', shortcut: 'i')]
        ?int $companyId = null,
    ): int {
        if ($companyId) {
            $company = $this->companyRepository->getEntity($companyId);
            $this->processUpdateCompany($company, $output);

            return ExitCode::SUCCESS;
        }

        foreach ($this->companyRepository->getEntities() as $company) {
            $this->processUpdateCompany($company, $output);
        }

        return ExitCode::SUCCESS;
    }

    private function processUpdateCompany(Company $company, OutputInterface $output): void
    {
        $output->writeln("<info>Updating the updated company name on leads table for company id {$company->getId()}.</info>");
        $this->companyLeadRepository->updateCompanyNameOnLeads($company);
    }
}
