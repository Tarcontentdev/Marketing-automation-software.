<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Form\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
final class LeadListAccess extends Constraint
{
    public string $message  = 'mailvotech.lead.lists.failed';

    public bool $allowEmpty = false;

    public function validatedBy(): string
    {
        return 'leadlist_access';
    }
}
