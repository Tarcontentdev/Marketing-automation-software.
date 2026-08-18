<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Executioner;

use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\CampaignBundle\Executioner\ContactFinder\Limiter\ContactLimiter;
use Symfony\Component\Console\Output\OutputInterface;

interface ExecutionerInterface
{
    /**
     * @return mixed
     */
    public function execute(Campaign $campaign, ContactLimiter $limiter, ?OutputInterface $output = null);
}
