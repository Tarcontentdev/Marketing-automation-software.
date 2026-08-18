<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\Tests\Sms;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\PluginBundle\Helper\IntegrationHelper;
use MailVotech\SmsBundle\Collection\RecipientCollection;
use MailVotech\SmsBundle\Entity\Sms;
use MailVotech\SmsBundle\Helper\DTO\SmsRecipientDTO;
use MailVotech\SmsBundle\Integration\Twilio\TwilioTransport;
use MailVotech\SmsBundle\Sms\BulkTransportInterface;
use MailVotech\SmsBundle\Sms\MMSTransportInterface;
use MailVotech\SmsBundle\Sms\TransportChain;
use MailVotech\SmsBundle\Sms\TransportInterface;
use PHPUnit\Framework\MockObject\MockObject;

final class TransportChainTest extends MailVotechMysqlTestCase
{
    private TransportChain $transportChain;

    private MockObject&TransportInterface $twilioTransport;

    /**
     * Call protected/private method of a class.
     *
     * @param object            $object     Instantiated object that we will run method on
     * @param string            $methodName Method name to call
     * @param array<int, mixed> $parameters array of parameters to pass into method
     *
     * @return mixed method return
     *
     * @throws \ReflectionException
     */
    public function invokeMethod(object $object, string $methodName, array $parameters = []): mixed
    {
        $reflection = new \ReflectionClass($object::class);
        $method     = $reflection->getMethod($methodName);

        return $method->invokeArgs($object, $parameters);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->transportChain = new TransportChain(
            'mailvotech.test.twilio.mock',
            self::getContainer()->get(IntegrationHelper::class)
        );

        $this->twilioTransport = $this->createMock(TwilioTransport::class);

        $this->twilioTransport
            ->method('sendSMS')
            ->willReturn('lol');
    }

    public function testAddTransport(): void
    {
        $count = count($this->transportChain->getTransports());

        $this->transportChain->addTransport('mailvotech.transport.test', self::getContainer()->get(TwilioTransport::class), 'mailvotech.transport.test', 'Twilio');

        $this->assertCount($count + 1, $this->transportChain->getTransports());
    }

    public function testSendSms(): void
    {
        $this->testAddTransport();

        $this->transportChain->addTransport('mailvotech.test.twilio.mock', $this->twilioTransport, 'mailvotech.test.twilio.mock', 'Twilio');

        $lead = new Lead();
        $lead->setMobile('+123456789');

        try {
            $this->transportChain->sendSms($lead, 'Yeah');
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->assertSame('Primary SMS transport is not enabled', $message);
        }
    }

    public function testSendBatchSms(): void
    {
        $bulkSmsTransport = new class() implements BulkTransportInterface {
            public function sendBatchSms(RecipientCollection $collection, string $content): RecipientCollection
            {
                foreach ($collection as &$recipient) {
                    $recipient->setResult(true);
                }

                return $collection;
            }

            public function sendSms(Lead $lead, $content): bool
            {
                return true;
            }
        };
        $this->createDataAndAssertSendMessage($bulkSmsTransport);
    }

    public function testSendMessage(): void
    {
        $mmsTransport = new class() implements TransportInterface, MMSTransportInterface {
            public function sendMms(Lead $lead, string $content, array $media): bool
            {
                return true;
            }

            public function sendSms(Lead $lead, $content): bool
            {
                return true;
            }
        };
        $this->createDataAndAssertSendMessage($mmsTransport);
    }

    private function createDataAndAssertSendMessage(TransportInterface $transport): void
    {
        $transportChain = new class('mailvotech.test.bulktwilio.mock', self::getContainer()->get(IntegrationHelper::class)) extends TransportChain {
            public function getEnabledTransports(): array
            {
                $transports = $this->getTransports();

                return array_map(fn (array $v): TransportInterface => $v['service'], $transports);
            }
        };

        $transportChain->addTransport('mailvotech.test.bulktwilio.mock', $transport, 'mailvotech.test.bulktwilio.mock', 'BulkTwilio');

        $lead1 = new Lead();
        $lead1->setMobile('+123456789');
        $lead1->setId(1);

        $lead2 = new Lead();
        $lead2->setMobile('+123456790');
        $lead2->setId(2);

        $recipientCollection = new RecipientCollection(new Sms());
        $recipientCollection->append(new SmsRecipientDTO($lead1, [], 'Yeah'));
        $recipientCollection->append(new SmsRecipientDTO($lead2, [], 'Yeah'));

        if ($transport instanceof MMSTransportInterface) {
            $recipientCollection = $transportChain->sendMMS($recipientCollection, ['test.png']);
        } elseif ($transport instanceof BulkTransportInterface) {
            $recipientCollection = $transportChain->sendBatchSms($recipientCollection, 'Yeah');
        }

        $sentCount = 0;
        foreach ($recipientCollection as $recipient) {
            if ($recipient->getResult()) {
                ++$sentCount;
            }
        }

        $this->assertSame(2, $sentCount);
    }
}
