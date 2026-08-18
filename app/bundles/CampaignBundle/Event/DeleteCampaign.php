<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Event;

use MailVotech\CampaignBundle\Entity\Campaign;

final class DeleteCampaign extends \Symfony\Contracts\EventDispatcher\Event
{
    public function __construct(
        private readonly Campaign $campaign,
    ) {
    }

    public function getCampaign(): Campaign
    {
        return $this->campaign;
    }
}
