<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Form\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
final class InfiniteLoop extends Constraint
{
}
