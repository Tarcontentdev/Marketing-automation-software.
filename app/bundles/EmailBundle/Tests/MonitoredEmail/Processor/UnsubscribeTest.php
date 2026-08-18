<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\MonitoredEmail\Processor;

use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Entity\Stat;
use MailVotech\EmailBundle\MonitoredEmail\Message;
use MailVotech\EmailBundle\MonitoredEmail\Processor\Unsubscribe;
use MailVotech\EmailBundle\MonitoredEmail\Search\ContactFinder;
use MailVotech\EmailBundle\MonitoredEmail\Search\Result;
use MailVotech\EmailBundle\Tests\MonitoredEmail\Transport\TestTransport;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Model\DoNotContact;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\TestDox;
use Symfony\Component\Mailer\Transport\NullTransport;

final class UnsubscribeTest extends \PHPUnit\Framework\TestCase
{
    #[TestDox('Test that the transport interface processes the message appropriately')]
    public function testProcessorInterfaceProcessesMessage(): void
    {
        $transport     = new TestTransport();
        $contactFinder = $this->createMock(ContactFinder::class);
        $contactFinder->method('find')
            ->willReturnCallback(
                function ($email): Result {
                    $stat = new Stat();

                    $lead = new Lead();
                    $lead->setEmail($email);
                    $stat->setLead($lead);

                    $email = new Email();
                    $stat->setEmail($email);

                    $result = new Result();
                    $result->setStat($stat);
                    $result->setContacts(
                        [
                            $lead,
                        ]
                    );

                    return $result;
                }
            );

        $translator = $this->createStub(Translator::class);

        $logger = $this->createStub(Logger::class);

        $doNotContact = $this->createStub(DoNotContact::class);

        $processor = new Unsubscribe($transport, $contactFinder, $translator, $logger, $doNotContact);

        $message = new Message();
        $this->assertTrue($processor->process($message));
    }

    #[TestDox('Test that the message is processed appropriately')]
    public function testContactIsFoundFromMessageAndDncRecordAdded(): void
    {
        $transport     = new NullTransport();
        $contactFinder = $this->createMock(ContactFinder::class);
        $contactFinder->method('find')
            ->willReturnCallback(
                function ($email): Result {
                    $stat = new Stat();

                    $lead = new Lead();
                    $lead->setEmail($email);
                    $stat->setLead($lead);

                    $email = new Email();
                    $stat->setEmail($email);

                    $result = new Result();
                    $result->setStat($stat);
                    $result->setContacts(
                        [
                            $lead,
                        ]
                    );

                    return $result;
                }
            );

        $translator = $this->createStub(Translator::class);

        $logger = $this->createStub(Logger::class);

        $doNotContact = $this->createStub(DoNotContact::class);

        $processor = new Unsubscribe($transport, $contactFinder, $translator, $logger, $doNotContact);

        $message     = new Message();
        $message->to = ['contact+unsubscribe_123abc@test.com' => null];
        $this->assertTrue($processor->process($message));
    }
}
