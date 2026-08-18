<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Form\Validator\Constraints;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
final class SegmentInUse extends Constraint
{
    /**
     * @param string[]|null $groups
     */
    #[HasNamedArguments]
    public function __construct(
        public string $message = 'mailvotech.lead_list.is_in_use.unpublish',
        ?array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct(null, $groups, $payload);
    }

    public function validatedBy(): string
    {
        return 'segment_in_use';
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
