<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Service;

use MailVotech\CoreBundle\Entity\OptimisticLockInterface;

interface OptimisticLockServiceInterface
{
    public function incrementVersion(OptimisticLockInterface $entity): void;

    public function resetVersion(OptimisticLockInterface $entity): void;

    public function acquireLock(OptimisticLockInterface $entity, int $expectedVersion = OptimisticLockInterface::INITIAL_VERSION): bool;
}
