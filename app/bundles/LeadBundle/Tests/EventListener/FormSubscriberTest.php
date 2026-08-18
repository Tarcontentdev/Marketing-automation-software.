<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\EventListener;

use MailVotech\CoreBundle\Entity\IpAddress;
use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\FormBundle\Entity\Action;
use MailVotech\FormBundle\Entity\Form;
use MailVotech\FormBundle\Entity\Submission;
use MailVotech\FormBundle\Event\SubmissionEvent;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadFieldRepository;
use MailVotech\LeadBundle\EventListener\FormSubscriber;
use MailVotech\LeadBundle\LeadEvents;
use MailVotech\LeadBundle\Model\DoNotContact;
use MailVotech\LeadBundle\Model\FieldModel;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\LeadBundle\Tracker\ContactTracker;
use MailVotech\PointBundle\Model\PointGroupModel;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

final class FormSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject&LeadModel
     */
    private MockObject $leadModel;

    private FormSubscriber $subscriber;

    /**
     * @var MockObject&ContactTracker
     */
    private MockObject $contactTracker;

    /**
     * @var MockObject&IpLookupHelper
     */
    private MockObject $ipLookupHelper;

    /**
     * @var MockObject&SubmissionEvent
     */
    private MockObject $submissionEvent;

    protected function setUp(): void
    {
        $this->leadModel          = $this->createMock(LeadModel::class);
        $this->contactTracker     = $this->createMock(ContactTracker::class);
        $this->ipLookupHelper     = $this->createMock(IpLookupHelper::class);
        $this->submissionEvent    = $this->createMock(SubmissionEvent::class);
        $this->subscriber         = new FormSubscriber(
            $this->leadModel,
            $this->contactTracker,
            $this->ipLookupHelper,
            $this->createStub(LeadFieldRepository::class),
            $this->createStub(PointGroupModel::class),
            $this->createStub(DoNotContact::class),
            $this->createStub(FieldModel::class)
        );
    }

    public function testOnFormSubmitActionChangePoints(): void
    {
        $this->contactTracker->method('getContact')->willReturn(new Lead());

        $this->ipLookupHelper->method('getIpAddress')->willReturn(new IpAddress());

        $submission = new Submission();
        $submission->setForm(new Form());
        $submission->setLead(new Lead());

        $submissionEvent = new SubmissionEvent($submission, [], [], new Request());

        $action = new Action();
        $action->setType('lead.pointschange');
        $action->setProperties(['points' => 1, 'operator' => 'plus']);
        $submissionEvent->setAction($action);

        $this->subscriber->onFormSubmitActionChangePoints($submissionEvent);

        $this->assertEquals(1, $submissionEvent->getSubmission()->getLead()->getPoints());
    }

    public function testThatTheLeadIsAddedToTheSegmentOnLeadOnSegmentsChangeEvent(): void
    {
        $this->submissionEvent
            ->method('getActionConfig')
            ->willReturn([
                'addToLists'      => 1,
                'removeFromLists' => null,
            ]);

        $this->leadModel->expects($this->once())->method('addToLists');
        $this->subscriber->onLeadSegmentsChange($this->submissionEvent);
    }

    public function testThatTheLeadIsRemovedFromTheSegmentOnLeadOnSegmentsChangeEvent(): void
    {
        $this->submissionEvent
            ->method('getActionConfig')
            ->willReturn([
                'removeFromLists' => 1,
                'addToLists'      => null,
            ]);

        $this->leadModel->expects($this->once())->method('removeFromLists');
        $this->subscriber->onLeadSegmentsChange($this->submissionEvent);
    }

    public function testThatTheObserverForTriggerOnLeadSegmentsChangeEventIsFired(): void
    {
        $subscribers = FormSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(LeadEvents::LEAD_ON_SEGMENTS_CHANGE, $subscribers);
        $this->assertSame(['onLeadSegmentsChange', 0], $subscribers[LeadEvents::LEAD_ON_SEGMENTS_CHANGE]);
    }
}
