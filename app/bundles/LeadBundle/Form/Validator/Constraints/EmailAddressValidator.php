<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Form\Validator\Constraints;

use MailVotech\EmailBundle\Exception\InvalidEmailException;
use MailVotech\EmailBundle\Helper\EmailValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class EmailAddressValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EmailValidator $emailValidator,
    ) {
    }

    /**
     * @param mixed $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!empty($value)) {
            try {
                $this->emailValidator->validate($value);
            } catch (InvalidEmailException $invalidEmailException) {
                $this->context->addViolation(
                    $invalidEmailException->getMessage()
                );
            }
        }
    }
}
