<?php

declare(strict_types=1);

namespace MailVotech\PointBundle\Tests\Unit\Model;

use Doctrine\ORM\EntityManager;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\EmailBundle\EmailEvents;
use MailVotech\EmailBundle\Form\Type\EmailToUserType;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadRepository;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\LeadBundle\Tracker\ContactTracker;
use MailVotech\PointBundle\Entity\TriggerEvent;
use MailVotech\PointBundle\Entity\TriggerEventRepository;
use MailVotech\PointBundle\Entity\TriggerRepository;
use MailVotech\PointBundle\Model\TriggerEventModel;
use MailVotech\PointBundle\Model\TriggerModel;
use MailVotech\PointBundle\PointEvents;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class TriggerModelTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject&EventDispatcherInterface
     */
    private MockObject $dispatcher;

    /**
     * @var MockObject&TriggerEventRepository
     */
    private MockObject $triggerEventRepository;

    private TriggerModel $triggerModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dispatcher             = $this->createMock(EventDispatcherInterface::class);
        $this->triggerEventRepository = $this->createMock(TriggerEventRepository::class);
        $this->triggerModel           = new TriggerModel(
            $this->createStub(IpLookupHelper::class),
            $this->createStub(LeadModel::class),
            $this->createStub(TriggerEventModel::class),
            $this->createStub(ContactTracker::class),
            $this->createStub(EntityManager::class),
            $this->createStub(CorePermissions::class),
            $this->dispatcher,
            $this->createStub(UrlGeneratorInterface::class),
            $this->createStub(Translator::class),
            $this->createStub(UserHelper::class),
            $this->createStub(LoggerInterface::class),
            $this->createStub(CoreParametersHelper::class),
            $this->createStub(TriggerRepository::class), // $triggerRepository
            $this->triggerEventRepository,
            $this->createStub(LeadRepository::class), // $leadRepository
        );

        // reset private property cachedEvents in TriggerModel instance
        $reflectionClass = new \ReflectionClass(TriggerModel::class);
        $property        = $reflectionClass->getProperty('cachedEvents');
        $property->setValue($this->triggerModel, []);
    }

    public function testTriggerEvent(): void
    {
        $triggerEvent  = new TriggerEvent();
        $contact       = new Lead();
        $dispatchCalls = new \ArrayObject();

        $triggerEvent->setType('email.send_to_user');

        $this->triggerEventRepository->expects($this->once())
            ->method('find')
            ->willReturn($triggerEvent);

        $this->dispatcher->expects($this->exactly(2))
            ->method('dispatch')
            ->willReturnCallback(function (object $event, ?string $eventName) use ($dispatchCalls, $contact, $triggerEvent): object {
                $dispatchCalls->append($eventName);

                if (PointEvents::TRIGGER_ON_BUILD === $eventName) {
                    // Emulate a subscriber:
                    $event->addEvent(
                        'email.send_to_user',
                        [
                            'group'           => 'mailvotech.email.point.trigger',
                            'label'           => 'mailvotech.email.point.trigger.send_email_to_user',
                            'formType'        => EmailToUserType::class,
                            'formTypeOptions' => ['update_select' => 'pointtriggerevent_properties_useremail_email'],
                            'formTheme'       => 'MailVotechEmailBundle:FormTheme\EmailSendList',
                            'eventName'       => EmailEvents::ON_SENT_EMAIL_TO_USER,
                        ]
                    );

                    return $event;
                }
                if (EmailEvents::ON_SENT_EMAIL_TO_USER === $eventName) {
                    $this->assertSame($contact, $event->getLead());
                    $this->assertSame($triggerEvent, $event->getTriggerEvent());

                    return $event;
                }
                $this->fail("Unexpected event name: {$eventName}");
            });

        $this->triggerModel->triggerEvent($triggerEvent->convertToArray(), $contact, true);

        // Assert both expected events were dispatched
        $this->assertContains(PointEvents::TRIGGER_ON_BUILD, $dispatchCalls);
        $this->assertContains(EmailEvents::ON_SENT_EMAIL_TO_USER, $dispatchCalls);
        $this->assertCount(2, $dispatchCalls);
    }
}
