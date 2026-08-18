<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Entity;

use MailVotech\LeadBundle\Entity\Lead;

/**
 * Interface EmailReplyRepositoryInterface.
 */
interface EmailReplyRepositoryInterface
{
    /**
     * @param int|Lead $leadId
     * @param array    $options
     *
     * @return array
     */
    public function getByLeadIdForTimeline($leadId, $options);
}
