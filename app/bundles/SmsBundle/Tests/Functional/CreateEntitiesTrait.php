<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\Tests\Functional;

use MailVotech\SmsBundle\Entity\Sms;

trait CreateEntitiesTrait
{
    private function createAnSms(string $name, string $message, bool $isPublished = true, string $locale = 'en'): Sms
    {
        $sms = new Sms();
        $sms->setName($name);
        $sms->setMessage($message);
        $sms->setLanguage($locale);
        $sms->setIsPublished($isPublished);

        return $sms;
    }
}
