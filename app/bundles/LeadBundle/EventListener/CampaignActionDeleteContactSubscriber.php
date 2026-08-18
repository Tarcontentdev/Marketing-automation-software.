<?php

namespace MailVotech\LeadBundle\EventListener;

use MailVotech\CampaignBundle\CampaignEvents;
use MailVotech\CampaignBundle\Event\CampaignBuilderEvent;
use MailVotech\CampaignBundle\Event\PendingEvent;
use MailVotech\CampaignBundle\Helper\RemovedContactTracker;
use MailVotech\LeadBundle\LeadEvents;
use MailVotech\LeadBundle\Model\LeadModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CampaignActionDeleteContactSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LeadModel $leadModel,
        private RemovedContactTracker $removedContactTracker,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CampaignEvents::CAMPAIGN_ON_BUILD             => ['configureAction', 0],
            LeadEvents::ON_CAMPAIGN_ACTION_DELETE_CONTACT => ['deleteContacts', 0],
        ];
    }

    public function configureAction(CampaignBuilderEvent $event): void
    {
        $event->addAction(
            'lead.deletecontact',
            [
                'label'                  => 'mailvotech.lead.lead.events.delete',
                'description'            => 'mailvotech.lead.lead.events.delete_descr',
                // Kept for BC in case plugins are listening to the shared trigger
                'eventName'              => LeadEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                'batchEventName'         => LeadEvents::ON_CAMPAIGN_ACTION_DELETE_CONTACT,
                'connectionRestrictions' => [
                    'target' => [
                        'decision'  => ['none'],
                        'action'    => ['none'],
                        'condition' => ['none'],
                    ],
                ],
            ]
        );
    }

    public function deleteContacts(PendingEvent $event): void
    {
        $contactIds = $event->getContactIds();

        $this->removedContactTracker->addRemovedContacts(
            $event->getEvent()->getCampaign()->getId(),
            $contactIds
        );

        $this->leadModel->deleteEntities($contactIds);

        $event->passAll();
    }
}
