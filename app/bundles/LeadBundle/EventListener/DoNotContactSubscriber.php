<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\EventListener;

use MailVotech\LeadBundle\Event\DoNotContactAddEvent;
use MailVotech\LeadBundle\Event\DoNotContactRemoveEvent;
use MailVotech\LeadBundle\Model\DoNotContact;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class DoNotContactSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private DoNotContact $doNotContact,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DoNotContactAddEvent::ADD_DONOT_CONTACT       => ['addDncForLead', 0],
            DoNotContactRemoveEvent::REMOVE_DONOT_CONTACT => ['removeDncForLead', 0],
        ];
    }

    public function removeDncForLead(DoNotContactRemoveEvent $doNotContactRemoveEvent): void
    {
        $this->doNotContact->removeDncForContact(
            $doNotContactRemoveEvent->getLead()->getId(),
            $doNotContactRemoveEvent->getChannel(),
            $doNotContactRemoveEvent->getPersist()
        );
    }

    public function addDncForLead(DoNotContactAddEvent $doNotContactAddEvent): void
    {
        if (empty($doNotContactAddEvent->getLead()->getId())) {
            $this->doNotContact->createDncRecord(
                $doNotContactAddEvent->getLead(),
                $doNotContactAddEvent->getChannel(),
                $doNotContactAddEvent->getReason(),
                $doNotContactAddEvent->getComments()
            );
        } else {
            $this->doNotContact->addDncForContact(
                $doNotContactAddEvent->getLead(),
                $doNotContactAddEvent->getChannel(),
                $doNotContactAddEvent->getReason(),
                $doNotContactAddEvent->getComments(),
                $doNotContactAddEvent->isPersist(),
                $doNotContactAddEvent->isCheckCurrentStatus(),
                $doNotContactAddEvent->isOverride()
            );
        }
    }
}
