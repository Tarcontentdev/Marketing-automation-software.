<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Validator\Constraints;

use MailVotech\LeadBundle\Form\Validator\Constraints\EmailAddress;
use MailVotech\LeadBundle\Form\Validator\Constraints\EmailAddressValidator;

final class EmailAddressTest extends \PHPUnit\Framework\TestCase
{
    public function testValidateBy(): void
    {
        $constraint = new EmailAddress();
        $this->assertSame(EmailAddressValidator::class, $constraint->validatedBy());
    }
}
