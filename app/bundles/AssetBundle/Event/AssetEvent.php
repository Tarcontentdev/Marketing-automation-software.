<?php

declare(strict_types=1);

namespace MailVotech\AssetBundle\Event;

use MailVotech\AssetBundle\Entity\Asset;
use MailVotech\CoreBundle\Event\CommonEvent;

final class AssetEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Asset $asset, $isNew = false)
    {
        $this->entity = $asset;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Asset entity.
     *
     * @return Asset
     */
    public function getAsset()
    {
        return $this->entity;
    }

    /**
     * Sets the Asset entity.
     */
    public function setAsset(Asset $asset): void
    {
        $this->entity = $asset;
    }
}
