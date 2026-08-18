<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Deduplicate;

use MailVotech\LeadBundle\Entity\Company;
use MailVotech\LeadBundle\Entity\CompanyRepository;
use MailVotech\LeadBundle\Exception\UniqueFieldNotFoundException;
use MailVotech\LeadBundle\Field\FieldsWithUniqueIdentifier;
use MailVotech\LeadBundle\Model\FieldModel;

class CompanyDeduper
{
    use DeduperTrait;

    public function __construct(
        FieldModel $fieldModel,
        FieldsWithUniqueIdentifier $fieldsWithUniqueIdentifier,
        private CompanyRepository $companyRepository,
    ) {
        $this->fieldModel                 = $fieldModel;
        $this->fieldsWithUniqueIdentifier = $fieldsWithUniqueIdentifier;
        $this->object                     = 'company';
    }

    /**
     * @return Company[]
     *
     * @throws UniqueFieldNotFoundException
     */
    public function checkForDuplicateCompanies(array $queryFields): array
    {
        $uniqueData = $this->getUniqueData($queryFields);
        if ([] === $uniqueData) {
            throw new UniqueFieldNotFoundException();
        }

        return $this->companyRepository->getCompaniesByUniqueFields($uniqueData);
    }
}
