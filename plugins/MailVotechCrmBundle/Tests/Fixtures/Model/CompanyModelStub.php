<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechCrmBundle\Tests\Fixtures\Model;

use MailVotech\EmailBundle\Helper\EmailValidator;
use MailVotech\LeadBundle\Deduplicate\CompanyDeduper;
use MailVotech\LeadBundle\Model\CompanyModel;
use MailVotech\LeadBundle\Model\FieldModel;

class CompanyModelStub extends CompanyModel
{
    public function setFieldModel(FieldModel $fieldModel): void
    {
        $this->leadFieldModel = $fieldModel;
    }

    public function setEmailValidator(EmailValidator $validator): void
    {
        $this->emailValidator = $validator;
    }

    public function setCompanyDeduper(CompanyDeduper $companyDeduper): void
    {
        $this->companyDeduper = $companyDeduper;
    }
}
