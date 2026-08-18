<?php

namespace MailVotech\EmailBundle\Validator;

use MailVotech\CoreBundle\Form\DataTransformer\ArrayStringTransformer;
use MailVotech\EmailBundle\Exception\InvalidEmailException;
use MailVotech\EmailBundle\Helper\EmailValidator;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class MultipleEmailsValidValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EmailValidator $emailValidator,
    ) {
    }

    /**
     * @param string $emailsInString
     */
    public function validate($emailsInString, Constraint $constraint): void
    {
        if (null === $emailsInString || '' === $emailsInString) {
            return;
        }

        $transformer = new ArrayStringTransformer();
        $emails      = $transformer->reverseTransform($emailsInString);

        foreach ($emails as $email) {
            try {
                $this->emailValidator->validate($email);
            } catch (InvalidEmailException $e) {
                $this->context->buildViolation('mailvotech.email.multiple_emails.not_valid', ['%email%' => $e->getMessage()])
                    ->addViolation();

                return;
            }
        }
    }
}
