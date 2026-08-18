<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechCrmBundle\Api;

use MailVotechPlugin\MailVotechCrmBundle\Integration\CrmAbstractIntegration;

/**
 * @method createLead(array<string, mixed> $fields, $lead)
 */
abstract class CrmApi
{
    public function __construct(
        protected CrmAbstractIntegration $integration,
    ) {
    }
}
