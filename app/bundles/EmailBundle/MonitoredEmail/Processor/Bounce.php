<?php

namespace MailVotech\EmailBundle\MonitoredEmail\Processor;

use MailVotech\CoreBundle\Helper\DateTimeHelper;
use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Entity\Stat;
use MailVotech\EmailBundle\Mailer\Transport\BounceProcessorInterface;
use MailVotech\EmailBundle\Model\EmailStatModel;
use MailVotech\EmailBundle\MonitoredEmail\Exception\BounceNotFound;
use MailVotech\EmailBundle\MonitoredEmail\Message;
use MailVotech\EmailBundle\MonitoredEmail\Processor\Bounce\BouncedEmail;
use MailVotech\EmailBundle\MonitoredEmail\Processor\Bounce\Parser;
use MailVotech\EmailBundle\MonitoredEmail\Search\ContactFinder;
use MailVotech\LeadBundle\Model\DoNotContact;
use MailVotech\LeadBundle\Model\LeadModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Bounce implements ProcessorInterface
{
    private const RETRY_COUNT = 5;

    /**
     * @var string
     */
    protected $bouncerAddress;

    /**
     * @var Message
     */
    protected $message;

    public function __construct(
        protected TransportInterface $transport,
        protected ContactFinder $contactFinder,
        protected EmailStatModel $emailStatModel,
        protected LeadModel $leadModel,
        protected TranslatorInterface $translator,
        protected LoggerInterface $logger,
        protected DoNotContact $doNotContact,
    ) {
    }

    public function process(Message $message): bool
    {
        $this->message = $message;
        $bounce        = false;

        $this->logger->debug('MONITORED EMAIL: Processing message ID '.$this->message->id.' for a bounce');

        // Does the transport have special handling such as Amazon SNS?
        if ($this->transport instanceof BounceProcessorInterface) {
            try {
                $bounce = $this->transport->processBounce($this->message);
            } catch (BounceNotFound) {
                // Attempt to parse a bounce the standard way
            }
        }

        if (!$bounce) {
            try {
                $bounce = (new Parser($this->message))->parse();
            } catch (BounceNotFound) {
                return false;
            }
        }

        $searchResult = $this->contactFinder->find($bounce->getContactEmail(), $bounce->getBounceAddress());
        if (!$contacts = $searchResult->getContacts()) {
            // No contacts found so bail
            return false;
        }

        $stat    = $searchResult->getStat();
        $channel = 'email';
        if ($stat) {
            // Update stat entry
            $this->updateStat($stat, $bounce);

            if ($stat->getEmail() instanceof Email) {
                // We know the email ID so set it to append to the the DNC record
                $channel = ['email' => $stat->getEmail()->getId()];
            }
        }

        $comments = $this->translator->trans('mailvotech.email.bounce.reason.'.$bounce->getRuleCategory());
        foreach ($contacts as $contact) {
            $this->doNotContact->addDncForContact($contact->getId(), $channel, \MailVotech\LeadBundle\Entity\DoNotContact::BOUNCED, $comments);
        }

        return true;
    }

    protected function updateStat(Stat $stat, BouncedEmail $bouncedEmail): void
    {
        $dtHelper    = new DateTimeHelper();
        $openDetails = $stat->getOpenDetails();

        if (!isset($openDetails['bounces'])) {
            $openDetails['bounces'] = [];
        }

        $openDetails['bounces'][] = [
            'datetime' => $dtHelper->toUtcString(),
            'reason'   => $bouncedEmail->getRuleCategory(),
            'code'     => $bouncedEmail->getRuleNumber(),
            'type'     => $bouncedEmail->getType(),
        ];

        $stat->setOpenDetails($openDetails);

        $retryCount = $stat->getRetryCount();
        ++$retryCount;
        $stat->setRetryCount($retryCount);

        if ($bouncedEmail->isFinal() || $retryCount >= self::RETRY_COUNT) {
            $stat->setIsFailed(true);
        }

        $this->emailStatModel->saveEntity($stat);
    }
}
