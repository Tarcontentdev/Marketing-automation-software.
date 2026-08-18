<?php

declare(strict_types=1);

namespace MailVotech\ChannelBundle\Tests\EventListener;

use Doctrine\Common\Collections\ArrayCollection;
use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\CampaignBundle\Entity\Event;
use MailVotech\CampaignBundle\Entity\LeadEventLog;
use MailVotech\CampaignBundle\Event\CampaignExecutionEvent;
use MailVotech\CampaignBundle\Event\PendingEvent;
use MailVotech\CampaignBundle\EventCollector\Accessor\Event\ActionAccessor;
use MailVotech\CampaignBundle\EventCollector\EventCollector;
use MailVotech\CampaignBundle\Executioner\Dispatcher\ActionDispatcher;
use MailVotech\CampaignBundle\Executioner\Dispatcher\LegacyEventDispatcher;
use MailVotech\CampaignBundle\Executioner\Scheduler\EventScheduler;
use MailVotech\ChannelBundle\ChannelEvents;
use MailVotech\ChannelBundle\EventListener\CampaignSubscriber;
use MailVotech\ChannelBundle\Form\Type\MessageSendType;
use MailVotech\ChannelBundle\Model\MessageModel;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\EmailBundle\EmailEvents;
use MailVotech\EmailBundle\Form\Type\EmailListType;
use MailVotech\EmailBundle\Form\Type\EmailSendType;
use MailVotech\LeadBundle\Entity\DoNotContact;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Tracker\ContactTracker;
use MailVotech\SmsBundle\Entity\Sms;
use MailVotech\SmsBundle\Form\Type\SmsSendType;
use MailVotech\SmsBundle\SmsEvents;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class CampaignSubscriberTest extends \PHPUnit\Framework\TestCase
{
    private EventDispatcher $dispatcher;

    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();

        $messageModel = $this->createMock(MessageModel::class);

        $messageModel->method('getChannels')
            ->willReturn(
                [
                    'email' => [
                        'campaignAction'             => 'email.send',
                        'campaignDecisionsSupported' => [
                            'email.open',
                            'page.pagehit',
                            'asset.download',
                            'form.submit',
                        ],
                        'lookupFormType'             => EmailListType::class,
                    ],
                    'sms'   => [
                        'campaignAction'             => 'sms.send_text_sms',
                        'campaignDecisionsSupported' => [
                            'page.pagehit',
                            'asset.download',
                            'form.submit',
                        ],
                        'lookupFormType'             => 'sms_list',
                        'repository'                 => Sms::class,
                    ],
                ]
            );

        $messageModel->method('getMessageChannels')
            ->willReturn(
                [
                    'email' => [
                        'id'         => 2,
                        'channel'    => 'email',
                        'channel_id' => 2,
                        'properties' => [],
                    ],
                    'sms'   => [
                        'id'         => 1,
                        'channel'    => 'sms',
                        'channel_id' => 1,
                        'properties' => [],
                    ],
                ]
            );

        $scheduler = $this->createMock(EventScheduler::class);

        $legacyDispatcher = new LegacyEventDispatcher(
            $this->dispatcher,
            $scheduler,
            new NullLogger(),
            $this->createStub(ContactTracker::class)
        );

        $eventDispatcher = new ActionDispatcher(
            $this->dispatcher,
            new NullLogger(),
            $scheduler,
            $legacyDispatcher
        );

        $eventCollector = $this->createMock(EventCollector::class);

        $eventCollector->method('getEventConfig')
            ->willReturnCallback(
                function (Event $event) {
                    switch ($event->getType()) {
                        case 'email.send':
                            return new ActionAccessor(
                                [
                                    'label'                => 'mailvotech.email.campaign.event.send',
                                    'description'          => 'mailvotech.email.campaign.event.send_descr',
                                    'batchEventName'       => EmailEvents::ON_CAMPAIGN_BATCH_ACTION,
                                    'formType'             => EmailSendType::class,
                                    'formTypeOptions'      => ['update_select' => 'campaignevent_properties_email', 'with_email_types' => true],
                                    'formTheme'            => 'MailVotechEmailBundle:FormTheme\EmailSendList',
                                    'channel'              => 'email',
                                    'channelIdField'       => 'email',
                                ]
                            );

                        case 'sms.send_text_sms':
                            return new ActionAccessor(
                                [
                                    'label'            => 'mailvotech.campaign.sms.send_text_sms',
                                    'description'      => 'mailvotech.campaign.sms.send_text_sms.tooltip',
                                    'eventName'        => SmsEvents::ON_CAMPAIGN_TRIGGER_ACTION,
                                    'formType'         => SmsSendType::class,
                                    'formTypeOptions'  => ['update_select' => 'campaignevent_properties_sms'],
                                    'formTheme'        => 'MailVotechSmsBundle:FormTheme\SmsSendList',
                                    'timelineTemplate' => '@MailVotechSms/SubscribedEvents/Timeline/index.html.twig',
                                    'channel'          => 'sms',
                                    'channelIdField'   => 'sms',
                                ]
                            );
                    }
                }
            );

        $campaignSubscriber = new CampaignSubscriber(
            $messageModel,
            $eventDispatcher,
            $eventCollector,
            new NullLogger(),
            $this->createStub(Translator::class)
        );

        $this->dispatcher->addSubscriber($campaignSubscriber);
        $this->dispatcher->addListener(EmailEvents::ON_CAMPAIGN_BATCH_ACTION, $this->sendMarketingMessageEmail(...));
        $this->dispatcher->addListener(SmsEvents::ON_CAMPAIGN_TRIGGER_ACTION, $this->sendMarketingMessageSms(...));
    }

    public function testCorrectChannelIsUsed(): void
    {
        $event  = $this->getEvent();
        $config = new ActionAccessor(
            [
                'label'                  => 'mailvotech.channel.message.send.marketing.message',
                'description'            => 'mailvotech.channel.message.send.marketing.message.descr',
                'batchEventName'         => ChannelEvents::ON_CAMPAIGN_BATCH_ACTION,
                'formType'               => MessageSendType::class,
                'formTheme'              => 'MailVotechChannelBundle:FormTheme\MessageSend',
                'channel'                => 'channel.message',
                'channelIdField'         => 'marketingMessage',
                'connectionRestrictions' => [
                    'target' => [
                        'decision' => [
                            'email.open',
                            'page.pagehit',
                            'asset.download',
                            'form.submit',
                        ],
                    ],
                ],
                'timelineTemplate'       => '@MailVotechChannel/SubscribedEvents/Timeline/index.html.twig',
                'timelineTemplateVars'   => [
                    'messageSettings' => [],
                ],
            ]
        );
        $logs   = $this->getLogs();

        $pendingEvent = new PendingEvent($config, $event, $logs);

        $this->dispatcher->dispatch($pendingEvent, ChannelEvents::ON_CAMPAIGN_BATCH_ACTION);

        $this->assertCount(0, $pendingEvent->getFailures());

        $successful = $pendingEvent->getSuccessful();

        // SMS should be noted as DNC
        $this->assertNotEmpty($successful->get(2)->getMetadata()['sms']['dnc']);

        // Nothing recorded for success
        $this->assertEmpty($successful->get(1)->getMetadata());
    }

    public function sendMarketingMessageEmail(PendingEvent $event): void
    {
        $contacts = $event->getContacts();
        $logs     = $event->getPending();
        $this->assertCount(1, $logs);

        if (1 === $contacts->first()->getId()) {
            // Processing priority 1 for contact 1, let's fail this one so that SMS is used
            $event->fail($logs->first(), 'just because');

            return;
        }

        if (2 === $contacts->first()->getId()) {
            // Processing priority 1 for contact 2 so let's pass it
            $event->pass($logs->first());

            return;
        }
    }

    /**
     * BC support for old campaign.
     */
    /**
     * @phpstan-ignore parameter.deprecatedClass
     */
    public function sendMarketingMessageSms(CampaignExecutionEvent $event): void
    {
        $lead = $event->getLead();
        if (1 === $lead->getId()) {
            $event->setResult(true);

            return;
        }

        if (2 === $lead->getId()) {
            $this->fail('Lead ID 2 is unsubscribed from SMS so this shouldn not have happened.');
        }
    }

    private function getEvent(): MockObject&Event
    {
        $event = $this->getMockBuilder(Event::class)
            ->onlyMethods(['getId'])
            ->getMock();
        $event->method('getId')
            ->willReturn(1);
        $event->setEventType(Event::TYPE_ACTION);
        $event->setType('message.send');
        $event->setChannel('channel.message');
        $event->setChannelId('1');
        $event->setProperties(
            [
                'canvasSettings'      => [
                    'droppedX' => '337',
                    'droppedY' => '155',
                ],
                'name'                => '',
                'triggerMode'         => 'immediate',
                'triggerDate'         => null,
                'triggerInterval'     => '1',
                'triggerIntervalUnit' => 'd',
                'anchor'              => 'leadsource',
                'properties'          => [
                    'marketingMessage' => '1',
                ],
                'type'                => 'message.send',
                'eventType'           => 'action',
                'anchorEventType'     => 'source',
                'campaignId'          => '1',
                '_token'              => 'q7FpcDX7iye6fBuBzsqMvQWKqW75lcD77jSmuNAEDXg',
                'buttons'             => [
                    'save' => '',
                ],
                'marketingMessage'    => '1',
            ]
        );
        $campaign = $this->createMock(Campaign::class);
        $campaign->method('getId')
            ->willReturn(1);

        $event->setCampaign($campaign);

        return $event;
    }

    private function getLogs(): ArrayCollection
    {
        $lead = $this->createMock(Lead::class);
        $lead->method('getId')
            ->willReturn(1);
        $lead->expects($this->once())
            ->method('getChannelRules')
            ->willReturn(
                [
                    'sms' => [
                        'dnc' => DoNotContact::IS_CONTACTABLE,
                    ],
                    'email' => [
                        'dnc' => DoNotContact::IS_CONTACTABLE,
                    ],
                ]
            );

        $log = $this->getMockBuilder(LeadEventLog::class)
            ->onlyMethods(['getLead', 'getId'])
            ->getMock();
        $log->method('getLead')
            ->willReturn($lead);
        $log->method('getId')
            ->willReturn(1);

        $lead2 = $this->createMock(Lead::class);
        $lead2->method('getId')
            ->willReturn(2);
        $lead2->expects($this->once())
            ->method('getChannelRules')
            ->willReturn(
                [
                    'email' => [
                        'dnc' => DoNotContact::IS_CONTACTABLE,
                    ],
                    'sms' => [
                        'dnc' => DoNotContact::UNSUBSCRIBED,
                    ],
                ]
            );

        $log2 = $this->getMockBuilder(LeadEventLog::class)
            ->onlyMethods(['getLead', 'getId'])
            ->getMock();
        $log2->method('getLead')
            ->willReturn($lead2);
        $log2->method('getId')
            ->willReturn(2);

        return new ArrayCollection([1 => $log, 2 => $log2]);
    }
}
