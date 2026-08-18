<?php

namespace MailVotech\ChannelBundle\Model;

use MailVotech\LeadBundle\Entity\DoNotContact as DNC;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Model\DoNotContact;
use MailVotech\LeadBundle\Model\LeadModel;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class ChannelActionModel
{
    public function __construct(
        private LeadModel $contactModel,
        private DoNotContact $doNotContact,
        private TranslatorInterface $translator,
    ) {
    }

    /**
     * Update channels and frequency rules.
     */
    public function update(array $contactIds, array $subscribedChannels): void
    {
        $contacts = $this->contactModel->getLeadsByIds($contactIds);

        foreach ($contacts as $contact) {
            if (!$this->contactModel->canEditContact($contact)) {
                continue;
            }

            $this->addChannels($contact, $subscribedChannels);
            $this->removeChannels($contact, $subscribedChannels);
        }
    }

    /**
     * Add contact's channels.
     * Only resubscribe if the contact did not opt out themselves.
     */
    private function addChannels(Lead $contact, array $subscribedChannels): void
    {
        $contactChannels = $this->contactModel->getContactChannels($contact);

        foreach ($subscribedChannels as $subscribedChannel) {
            if (!array_key_exists($subscribedChannel, $contactChannels)) {
                $contactable = $this->doNotContact->isContactable($contact, $subscribedChannel);
                if (DNC::UNSUBSCRIBED !== $contactable) {
                    $this->doNotContact->removeDncForContact($contact->getId(), $subscribedChannel);
                }
            }
        }
    }

    /**
     * Remove contact's channels.
     */
    private function removeChannels(Lead $contact, array $subscribedChannels): void
    {
        $allChannels = $this->contactModel->getPreferenceChannels();
        $dncChannels = array_diff($allChannels, $subscribedChannels);

        foreach ($dncChannels as $channel) {
            $this->doNotContact->addDncForContact(
                $contact->getId(),
                $channel,
                DNC::MANUAL,
                $this->translator->trans('mailvotech.lead.event.donotcontact_manual')
            );
        }
    }
}
