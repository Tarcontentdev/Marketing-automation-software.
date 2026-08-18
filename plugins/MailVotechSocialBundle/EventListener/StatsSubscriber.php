<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechSocialBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CoreBundle\EventListener\CommonStatsSubscriber;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotechPlugin\MailVotechSocialBundle\Entity\TweetStatRepository;

final class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(
        CorePermissions $security,
        EntityManagerInterface $entityManager,
        TweetStatRepository $tweetStatRepository,
    ) {
        parent::__construct($security, $entityManager);

        $table                     = $tweetStatRepository->getTableName();
        $this->repositories[]      = $tweetStatRepository;
        $this->permissions[$table] = ['tweet' => 'mailvotechSocial:tweets'];
    }
}
