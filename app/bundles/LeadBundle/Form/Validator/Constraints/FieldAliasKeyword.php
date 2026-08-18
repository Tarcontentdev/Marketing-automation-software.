<?php

namespace MailVotech\LeadBundle\Form\Validator\Constraints;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class FieldAliasKeyword extends Constraint
{
    /**
     * @param string[]|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        public string $message = 'mailvotech.lead.field.keyword.invalid',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function validatedBy(): string
    {
        return FieldAliasKeywordValidator::class;
    }
}
