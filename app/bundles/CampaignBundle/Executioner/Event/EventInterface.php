<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Executioner\Event;

use Doctrine\Common\Collections\ArrayCollection;
use MailVotech\CampaignBundle\EventCollector\Accessor\Event\AbstractEventAccessor;
use MailVotech\CampaignBundle\Executioner\Result\EvaluatedContacts;

interface EventInterface
{
    /**
     * @return EvaluatedContacts
     */
    public function execute(AbstractEventAccessor $config, ArrayCollection $logs);
}
