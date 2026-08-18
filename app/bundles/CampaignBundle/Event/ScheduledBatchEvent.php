<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Event;

use Doctrine\Common\Collections\ArrayCollection;
use MailVotech\CampaignBundle\Entity\Event;
use MailVotech\CampaignBundle\EventCollector\Accessor\Event\AbstractEventAccessor;

final class ScheduledBatchEvent extends AbstractLogCollectionEvent
{
    /**
     * @param bool $isReschedule
     */
    public function __construct(
        AbstractEventAccessor $config,
        Event $event,
        ArrayCollection $logs,
        private $isReschedule = false,
    ) {
        parent::__construct($config, $event, $logs);
    }

    /**
     * @return ArrayCollection
     */
    public function getScheduled()
    {
        return $this->logs;
    }

    /**
     * @return bool
     */
    public function isReschedule()
    {
        return $this->isReschedule;
    }
}
