<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Validator\Constraint;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class IsPostActionRedirectUrl extends Constraint
{
    /**
     * @param string[]|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        public string $message = 'mailvotech.form.form.postactionproperty_redirect.url',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }
}
