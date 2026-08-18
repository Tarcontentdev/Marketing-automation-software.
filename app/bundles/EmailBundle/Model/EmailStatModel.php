<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Model;

use MailVotech\EmailBundle\EmailEvents;
use MailVotech\EmailBundle\Entity\Stat;
use MailVotech\EmailBundle\Entity\StatRepository;
use MailVotech\EmailBundle\Event\EmailStatEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class EmailStatModel
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly StatRepository $statRepository,
    ) {
    }

    public function saveEntity(Stat $stat): void
    {
        $this->saveEntities([$stat]);
    }

    /**
     * @param Stat[] $stats
     */
    public function saveEntities(array $stats): void
    {
        $event = new EmailStatEvent($stats);

        $this->dispatcher->dispatch($event, EmailEvents::ON_EMAIL_STAT_PRE_SAVE);

        $this->statRepository->saveEntities($stats);

        $this->dispatcher->dispatch($event, EmailEvents::ON_EMAIL_STAT_POST_SAVE);
    }

    public function getRepository(): StatRepository
    {
        return $this->statRepository;
    }
}
