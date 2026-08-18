<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\MonitoredEmail\Processor;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use MailVotech\CoreBundle\Helper\EmailAddressHelper;
use MailVotech\EmailBundle\EmailEvents;
use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Entity\EmailReply;
use MailVotech\EmailBundle\Entity\Stat;
use MailVotech\EmailBundle\Entity\StatRepository;
use MailVotech\EmailBundle\Event\EmailReplyEvent;
use MailVotech\EmailBundle\Model\EmailStatModel;
use MailVotech\EmailBundle\MonitoredEmail\Message;
use MailVotech\EmailBundle\MonitoredEmail\Processor\Reply;
use MailVotech\EmailBundle\MonitoredEmail\Search\ContactFinder;
use MailVotech\EmailBundle\MonitoredEmail\Search\Result;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadRepository;
use MailVotech\LeadBundle\Tracker\ContactTracker;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\TestDox;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class ReplyTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject&StatRepository
     */
    private MockObject $statRepo;

    /**
     * @var MockObject&EmailStatModel
     */
    private MockObject $emailStatModel;

    /**
     * @var MockObject&ContactFinder
     */
    private MockObject $contactFinder;

    /**
     * @var MockObject&EventDispatcherInterface
     */
    private MockObject $dispatcher;

    /**
     * @var MockObject&ContactTracker
     */
    private MockObject $contactTracker;

    private Reply $processor;

    /**
     * @var MockObject&LeadRepository
     */
    private MockObject $leadRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->statRepo           = $this->createMock(StatRepository::class);
        $this->emailStatModel     = $this->createMock(EmailStatModel::class);
        $this->contactFinder      = $this->createMock(ContactFinder::class);
        $this->dispatcher         = $this->createMock(EventDispatcherInterface::class);
        $this->contactTracker     = $this->createMock(ContactTracker::class);
        $emailAddressHelper       = new EmailAddressHelper();
        $this->leadRepository     = $this->createMock(LeadRepository::class);
        $this->processor          = new Reply(
            $this->emailStatModel,
            $this->contactFinder,
            $this->dispatcher,
            $this->createStub(Logger::class),
            $this->contactTracker,
            $emailAddressHelper,
            $this->leadRepository
        );

        $this->emailStatModel->method('getRepository')->willReturn($this->statRepo);
    }

    #[TestDox('Test that the message is processed appropriately')]
    public function testContactIsFoundFromMessageAndDncRecordAdded(): void
    {
        // This tells us that a reply was found and processed
        $this->emailStatModel->expects($this->once())
            ->method('saveEntity');

        $this->leadRepository->expects($this->atLeastOnce())
            ->method('detachEntity');

        $this->contactFinder->method('findByHash')
            ->willReturnCallback(
                function ($hash): Result {
                    $stat = new Stat();
                    $stat->setTrackingHash($hash);

                    $lead = new Lead();
                    $lead->setEmail('contact@email.com');
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

        $message              = new Message();
        $message->fromAddress = 'contact@email.com';
        $message->textHtml    = <<<'BODY'
<img src="http://test.com/email/123abc.gif" />
BODY;

        $this->processor->process($message);
    }

    public function testCreateReplyByHashIfStatNotFound(): void
    {
        $trackingHash = '@Stat#';

        $this->statRepo->expects($this->once())
            ->method('findOneBy')
            ->with(['trackingHash' => $trackingHash])
            ->willReturn(null);

        $this->expectException(EntityNotFoundException::class);

        $this->processor->createReplyByHash($trackingHash, 'api-msg1d');
    }

    public function testCreateReplyByHash(): void
    {
        $trackingHash = '@Stat#';
        $stat         = $this->createMock(Stat::class);
        $contact      = $this->createStub(Lead::class);

        $this->statRepo->expects($this->once())
            ->method('findOneBy')
            ->with(['trackingHash' => $trackingHash])
            ->willReturn($stat);

        $stat->expects($this->once())
            ->method('setIsRead')
            ->with(true);

        $stat->expects($this->once())
            ->method('getDateRead')
            ->willReturn(null);

        $stat->expects($this->once())
            ->method('setDateRead')
            ->with($this->isInstanceOf(\DateTime::class));

        $stat
            ->method('getReplies')
            ->willReturn(new ArrayCollection());

        $stat->expects($this->once())
            ->method('addReply')
            ->willReturnCallback(function (EmailReply $emailReply) use ($stat): void {
                $this->assertSame($stat, $emailReply->getStat());
                $this->assertSame('api-msg1d', $emailReply->getMessageId());
            });

        $this->emailStatModel->expects($this->once())
            ->method('saveEntity')
            ->with($this->isInstanceOf(Stat::class));

        $stat->expects($this->exactly(2))
            ->method('getLead')
            ->willReturn($contact);

        $this->dispatcher->expects($this->once())
            ->method('hasListeners')
            ->with(EmailEvents::EMAIL_ON_REPLY)
            ->willReturn(true);

        $this->contactTracker->expects($this->once())
            ->method('setTrackedContact')
            ->with($contact);

        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(EmailReplyEvent::class), EmailEvents::EMAIL_ON_REPLY);

        $this->processor->createReplyByHash($trackingHash, 'api-msg1d');
    }
}
