<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\Event\CustomTemplateEvent;
use MailVotech\IntegrationsBundle\Entity\ObjectMappingRepository;
use MailVotech\IntegrationsBundle\Sync\SyncDataExchange\Internal\Object\Contact;
use MailVotech\LeadBundle\Entity\Lead;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class UIContactIntegrationsTabSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ObjectMappingRepository $objectMappingRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::VIEW_INJECT_CUSTOM_TEMPLATE => ['onTemplateRender', 0],
        ];
    }

    public function onTemplateRender(CustomTemplateEvent $event): void
    {
        if ('@MailVotechLead/Lead/lead.html.twig' === $event->getTemplate()) {
            $vars         = $event->getVars();
            $integrations = $vars['integrations'];

            /** @var Lead $contact */
            $contact = $vars['lead'];

            $objectMappings = $this->objectMappingRepository->getIntegrationMappingsForInternalObject(
                Contact::NAME,
                (int) $contact->getId()
            );

            foreach ($objectMappings as $objectMapping) {
                $integrations[] = [
                    'integration'           => $objectMapping->getIntegration(),
                    'integration_entity'    => $objectMapping->getIntegrationObjectName(),
                    'integration_entity_id' => $objectMapping->getIntegrationObjectId(),
                    'date_added'            => $objectMapping->getDateCreated(),
                    'last_sync_date'        => $objectMapping->getLastSyncDate(),
                ];
            }

            $vars['integrations'] = $integrations;

            $event->setVars($vars);
        }
    }
}
