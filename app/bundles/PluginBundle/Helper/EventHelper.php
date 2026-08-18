<?php

declare(strict_types=1);

namespace MailVotech\PluginBundle\Helper;

use MailVotech\LeadBundle\Entity\LeadRepository;
use MailVotech\PluginBundle\EventListener\PushToIntegrationTrait;

final class EventHelper
{
    use PushToIntegrationTrait;

    /**
     * @param array<string, mixed> $config
     */
    public static function pushLead(array $config, $lead, LeadRepository $leadRepository, IntegrationHelper $integrationHelper): bool
    {
        $contact = $leadRepository->getEntityWithPrimaryCompany($lead);

        self::setStaticIntegrationHelper($integrationHelper);
        $errors  = [];

        return self::pushIt($config, $contact, $errors);
    }
}
