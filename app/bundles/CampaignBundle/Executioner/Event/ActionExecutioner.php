<?php

namespace MailVotech\CampaignBundle\Executioner\Event;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use MailVotech\CampaignBundle\Entity\Event;
use MailVotech\CampaignBundle\Entity\LeadEventLog;
use MailVotech\CampaignBundle\EventCollector\Accessor\Event\AbstractEventAccessor;
use MailVotech\CampaignBundle\EventCollector\Accessor\Event\ActionAccessor;
use MailVotech\CampaignBundle\Executioner\Dispatcher\ActionDispatcher;
use MailVotech\CampaignBundle\Executioner\Exception\CannotProcessEventException;
use MailVotech\CampaignBundle\Executioner\Logger\EventLogger;
use MailVotech\CampaignBundle\Executioner\Result\EvaluatedContacts;
use MailVotech\CoreBundle\Service\OptimisticLockServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ActionExecutioner implements EventInterface
{
    public const TYPE = 'action';

    public function __construct(
        private readonly ActionDispatcher $dispatcher,
        private readonly EventLogger $eventLogger,
        private readonly OptimisticLockServiceInterface $optimisticLockService,
        #[Autowire(service: 'monolog.logger.mailvotech')]
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws CannotProcessEventException
     * @throws \MailVotech\CampaignBundle\Executioner\Dispatcher\Exception\LogNotProcessedException
     * @throws \MailVotech\CampaignBundle\Executioner\Dispatcher\Exception\LogPassedAndFailedException
     */
    public function execute(AbstractEventAccessor $config, ArrayCollection $logs): EvaluatedContacts
    {
        \assert($config instanceof ActionAccessor);

        $firstLog = $logs->first();
        if (!$firstLog instanceof LeadEventLog) {
            return new EvaluatedContacts();
        }

        $event = $firstLog->getEvent();

        if (Event::TYPE_ACTION !== $event->getEventType()) {
            throw new CannotProcessEventException('Cannot process event ID '.$event->getId().' as an action.');
        }

        $this->lockLogs($logs);

        // Execute to process the batch of contacts
        $pendingEvent = $this->dispatcher->dispatchEvent($config, $event, $logs);

        $passed = $this->eventLogger->extractContactsFromLogs($pendingEvent->getSuccessful());
        $failed = $this->eventLogger->extractContactsFromLogs($pendingEvent->getFailures());

        return new EvaluatedContacts($passed, $failed);
    }

    /**
     * @param Collection<LeadEventLog> $logs
     */
    private function lockLogs(Collection $logs): void
    {
        foreach ($logs as $key => $log) {
            if (!$this->optimisticLockService->acquireLock($log)) {
                $logs->remove($key);
                $this->logger->error(message: sprintf(
                    'Campaign event log ID "%s" was skipped as it had been executed already.',
                    $log->getId(),
                ));
            }
        }
    }
}
