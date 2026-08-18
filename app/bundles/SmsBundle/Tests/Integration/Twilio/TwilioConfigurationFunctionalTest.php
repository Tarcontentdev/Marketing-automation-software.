<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\Tests\Integration\Twilio;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\PluginBundle\Entity\Integration;
use MailVotech\SmsBundle\Integration\TwilioIntegration;
use MailVotech\SmsBundle\Tests\SmsTestHelperTrait;

final class TwilioConfigurationFunctionalTest extends MailVotechMysqlTestCase
{
    use SmsTestHelperTrait;

    public function testSaveTwilioConfig(): void
    {
        $this->configureTwilioWithArrayTransport();

        /** @var TwilioIntegration $integration */
        $integration = $this->getContainer()->get(TwilioIntegration::class);
        $this->assertInstanceOf(TwilioIntegration::class, $integration);

        $integrationRepository = $this->em->getRepository(Integration::class);

        $integrationConfig = $integrationRepository->findOneBy(['name' => $integration->getName()]);
        $this->assertInstanceOf(Integration::class, $integrationConfig);
        $this->assertSame('messaging_sid', $integrationConfig->getFeatureSettings()['messaging_service_sid']);
    }
}
