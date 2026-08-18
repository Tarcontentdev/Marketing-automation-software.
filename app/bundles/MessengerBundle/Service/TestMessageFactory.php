<?php

declare(strict_types=1);

namespace MailVotech\MessengerBundle\Service;

use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\MessengerBundle\Message\TestEmail;
use MailVotech\MessengerBundle\Message\TestFailed;
use MailVotech\MessengerBundle\Message\TestHit;

final readonly class TestMessageFactory
{
    public function __construct(
        private UserHelper $userHelper,
    ) {
    }

    public function crateMessageByDsnKey(string $key): object
    {
        return match ($key) {
            'messenger_dsn_email'  => new TestEmail($this->userHelper->getUser()->getId()),
            'messenger_dsn_hit'    => new TestHit($this->userHelper->getUser()->getId()),
            'messenger_dsn_failed' => new TestFailed($this->userHelper->getUser()->getId()),
            default                => throw new \InvalidArgumentException(sprintf('Unsupported key: "%s"', $key)),
        };
    }
}
