<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Validator\Constraints;

use MailVotech\LeadBundle\Validator\Constraints\Length;
use MailVotech\LeadBundle\Validator\Constraints\LengthValidator;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;

final class LengthValidatorTest extends \PHPUnit\Framework\TestCase
{
    #[DoesNotPerformAssertions]
    public function testValidate(): void
    {
        $constraint = new Length(['min' => 3]);
        $validator  = new LengthValidator();

        $validator->validate('valid', $constraint);
        // Not thrownig Symfony\Component\Validator\Exception\UnexpectedTypeException
        $validator->validate(['0', '1'], $constraint);
    }
}
