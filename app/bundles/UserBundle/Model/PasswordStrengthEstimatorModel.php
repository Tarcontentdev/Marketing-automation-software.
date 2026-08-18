<?php

declare(strict_types=1);

namespace MailVotech\UserBundle\Model;

use MailVotech\UserBundle\Event\PasswordStrengthValidateEvent;
use MailVotech\UserBundle\UserEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use ZxcvbnPhp\Zxcvbn as PasswordStrengthEstimator;

final readonly class PasswordStrengthEstimatorModel
{
    public const MINIMUM_PASSWORD_STRENGTH_ALLOWED = 3;

    private const DICTIONARY = [
        'mailvotech',
        'user',
        'lead',
        'bundle',
        'campaign',
        'company',
    ];

    private PasswordStrengthEstimator $passwordStrengthEstimator;

    public function __construct(
        private EventDispatcherInterface $dispatcher,
    ) {
        $this->passwordStrengthEstimator = new PasswordStrengthEstimator();
    }

    /**
     * @param string[] $dictionary
     */
    public function validate(?string $password, int $score = self::MINIMUM_PASSWORD_STRENGTH_ALLOWED, array $dictionary = self::DICTIONARY): bool
    {
        $isValid = $score <= $this->passwordStrengthEstimator->passwordStrength($password, $this->sanitizeDictionary($dictionary))['score'];

        $passwordStrengthValidateEvent = new PasswordStrengthValidateEvent($isValid, $password);
        $this->dispatcher->dispatch($passwordStrengthValidateEvent, UserEvents::USER_PASSWORD_STRENGTH_VALIDATION);

        return $passwordStrengthValidateEvent->isValid;
    }

    /**
     * @param string[] $dictionary
     *
     * @return string[]
     */
    private function sanitizeDictionary(array $dictionary): array
    {
        return array_unique(array_filter($dictionary));
    }
}
