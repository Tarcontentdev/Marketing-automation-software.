<?php

declare(strict_types=1);

namespace MailVotech\MessengerBundle\Validator;

use MailVotech\CoreBundle\Helper\Dsn\Dsn as CoreDsn;
use MailVotech\MessengerBundle\Validator\Dsn as DsnConstraint;
use Symfony\Component\Messenger\Transport\TransportFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class DsnValidator extends ConstraintValidator
{
    public function __construct(
        private readonly TransportFactory $transportFactory,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        if (!$constraint instanceof DsnConstraint) {
            throw new UnexpectedTypeException($constraint, DsnConstraint::class);
        }

        if (!$value) {
            return;
        }

        try {
            $dsn = CoreDsn::fromString($value);
        } catch (\InvalidArgumentException) {
            $this->context->addViolation('mailvotech.messenger.dsn.invalid_dsn');

            return;
        }

        if (!$this->transportFactory->supports($value, $dsn->getOptions())) {
            $this->context->addViolation('mailvotech.messenger.dsn.unsupported_scheme');
        }
    }
}
