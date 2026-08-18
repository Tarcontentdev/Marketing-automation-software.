<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\EmailBundle\Entity\Email;

class EmailEditSubmitEvent extends CommonEvent
{
    public function __construct(
        private readonly Email $previousEmail,
        private readonly Email $currentEmail,
        private readonly bool $saveAndClose,
        private readonly bool $apply,
        private readonly bool $saveAsDraft,
        private readonly bool $applyDraft,
        private readonly bool $discardDraft,
    ) {
    }

    public function getPreviousEmail(): Email
    {
        return $this->previousEmail;
    }

    public function getCurrentEmail(): Email
    {
        return $this->currentEmail;
    }

    public function isSaveAndClose(): bool
    {
        return $this->saveAndClose;
    }

    public function isApply(): bool
    {
        return $this->apply;
    }

    public function isSaveAsDraft(): bool
    {
        return $this->saveAsDraft;
    }

    public function isApplyDraft(): bool
    {
        return $this->applyDraft;
    }

    public function isDiscardDraft(): bool
    {
        return $this->discardDraft;
    }
}
