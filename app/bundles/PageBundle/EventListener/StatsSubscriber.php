<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CoreBundle\EventListener\CommonStatsSubscriber;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\PageBundle\Entity\Hit;
use MailVotech\PageBundle\Entity\RedirectRepository;
use MailVotech\PageBundle\Entity\TrackableRepository;
use MailVotech\PageBundle\Entity\VideoHit;

final class StatsSubscriber extends CommonStatsSubscriber
{
    public function __construct(
        CorePermissions $security,
        EntityManagerInterface $entityManager,
        RedirectRepository $redirectRepository,
        TrackableRepository $trackableRepository,
    ) {
        parent::__construct($security, $entityManager);
        $this->addContactRestrictedRepositories(
            [
                Hit::class,
                VideoHit::class,
            ]
        );

        $this->repositories[] = $redirectRepository;
        $this->repositories[] = $trackableRepository;
    }
}
