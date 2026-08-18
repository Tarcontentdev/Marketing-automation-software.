<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Validator\Constraints;

use MailVotech\LeadBundle\Validator\Constraints\Length;
use MailVotech\LeadBundle\Validator\Constraints\LengthValidator;

final class LengthTest extends \PHPUnit\Framework\TestCase
{
    public function testValidateBy(): void
    {
        $constraint = new Length(['min' => 3]);
        $this->assertSame(LengthValidator::class, $constraint->validatedBy());
    }
}
