<?php

namespace MailVotech\CampaignBundle\Service;

use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\CampaignBundle\Entity\EventRepository;
use MailVotech\CoreBundle\Service\FlashBag;
use MailVotech\EmailBundle\Entity\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CampaignAuditService
{
    public function __construct(
        private readonly FlashBag $flashBag,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly EventRepository $eventRepository,
    ) {
    }

    public function addWarningForUnpublishedEmails(Campaign $campaign): void
    {
        $emails = $this->eventRepository->getCampaignEmailEvents($campaign->getId());

        foreach ($emails as $email) {
            if (!$email->isPublished()) {
                $this->setEmailWarningFlashMessage($email);
            }
        }
    }

    private function setEmailWarningFlashMessage(Email $email): void
    {
        $this->flashBag->add(
            'mailvotech.core.notice.campaign.unpublished.email',
            [
                '%name%'      => $email->getName(),
                '%menu_link%' => 'mailvotech_email_index',
                '%url%'       => $this->urlGenerator->generate('mailvotech_email_action', [
                    'objectAction' => 'edit',
                    'objectId'     => $email->getId(),
                ]),
            ],
            FlashBag::LEVEL_WARNING,
        );
    }
}
