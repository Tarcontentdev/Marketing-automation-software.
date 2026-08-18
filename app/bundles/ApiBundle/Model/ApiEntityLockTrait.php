<?php

declare(strict_types=1);

namespace MailVotech\ApiBundle\Model;

trait ApiEntityLockTrait
{
    /**
     * Check if the entity is locked based on its checked-out status.
     */
    public function isApiLocked(object $entity): bool
    {
        return $this->isLocked($entity);
    }
}
