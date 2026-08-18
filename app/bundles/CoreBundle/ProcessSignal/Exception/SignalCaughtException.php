<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\ProcessSignal\Exception;

use MailVotech\CoreBundle\ProcessSignal\ProcessSignalState;

final class SignalCaughtException extends \Exception
{
    public function __construct(
        int $signal,
        private readonly ?ProcessSignalState $state = null,
    ) {
        parent::__construct(sprintf('Signal received: "%d"', $signal), $signal);
    }

    public function getState(): ?ProcessSignalState
    {
        return $this->state;
    }
}
