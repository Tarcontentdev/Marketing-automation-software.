<?php

namespace MailVotech\EmailBundle\MonitoredEmail\Processor;

use MailVotech\EmailBundle\Mailer\Transport\UnsubscriptionProcessorInterface;
use MailVotech\EmailBundle\MonitoredEmail\Exception\UnsubscriptionNotFound;
use MailVotech\EmailBundle\MonitoredEmail\Message;
use MailVotech\EmailBundle\MonitoredEmail\Processor\Unsubscription\Parser;
use MailVotech\EmailBundle\MonitoredEmail\Search\ContactFinder;
use MailVotech\LeadBundle\Entity\DoNotContact;
use MailVotech\LeadBundle\Model\DoNotContact as DoNotContactModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Unsubscribe implements ProcessorInterface
{
    private ?Message $message = null;

    public function __construct(
        private readonly TransportInterface $transport,
        private readonly ContactFinder $contactFinder,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger,
        private readonly DoNotContactModel $doNotContact,
    ) {
    }

    public function process(Message $message): bool
    {
        $this->message = $message;
        $this->logger->debug('MONITORED EMAIL: Processing message ID '.$this->message->id.' for an unsubscription');

        $unsubscription = false;

        // Does the transport have special handling like Amazon SNS
        if ($this->transport instanceof UnsubscriptionProcessorInterface) {
            try {
                $unsubscription = $this->transport->processUnsubscription($this->message);
            } catch (UnsubscriptionNotFound) {
                // Attempt to parse a unsubscription the standard way
            }
        }

        if (!$unsubscription) {
            try {
                $parser         = new Parser($message);
                $unsubscription = $parser->parse();
            } catch (UnsubscriptionNotFound) {
                // No stat found so bail as we won't consider this a reply
                $this->logger->debug('MONITORED EMAIL: Unsubscription email was not found');

                return false;
            }
        }

        $searchResult = $this->contactFinder->find($unsubscription->getContactEmail(), $unsubscription->getUnsubscriptionAddress());
        if (!$contacts = $searchResult->getContacts()) {
            // No contacts found so bail
            return false;
        }

        $stat    = $searchResult->getStat();
        $channel = 'email';
        if ($stat && $email = $stat->getEmail()) {
            // We know the email ID so set it to append to the the DNC record
            $channel = ['email' => $email->getId()];
        }

        $comments = $this->translator->trans('mailvotech.email.bounce.reason.unsubscribed');
        foreach ($contacts as $contact) {
            $this->doNotContact->addDncForContact($contact->getId(), $channel, DoNotContact::UNSUBSCRIBED, $comments);
        }

        return true;
    }
}
