<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use MailVotech\CoreBundle\Entity\OptimisticLockInterface;
use MailVotech\CoreBundle\Service\OptimisticLockServiceInterface;

#[AsDoctrineListener(Events::postUpdate)]
final readonly class OptimisticLockSubscriber
{
    public function __construct(
        private OptimisticLockServiceInterface $optimisticLockService,
    ) {
    }

    /**
     * If the object implements OptimisticLockInterface and is marked for incrementing the version, object's version column/field is incremented.
     */
    public function postUpdate(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();

        if (!$object instanceof OptimisticLockInterface || !$object->isMarkedForVersionIncrement()) {
            return;
        }

        $this->optimisticLockService->incrementVersion($object);
    }
}
