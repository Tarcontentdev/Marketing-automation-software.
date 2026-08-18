<?php

declare(strict_types=1);

namespace MailVotech\ProjectBundle\EventListener;

use MailVotech\ApiBundle\Event\ApiInitializeEvent;
use MailVotech\ApiBundle\Serializer\Exclusion\FieldInclusionStrategy;
use MailVotech\AssetBundle\Entity\Asset;
use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\ChannelBundle\Entity\Message;
use MailVotech\DynamicContentBundle\Entity\DynamicContent;
use MailVotech\EmailBundle\Entity\Email;
use MailVotech\FormBundle\Entity\Form;
use MailVotech\LeadBundle\Entity\Company;
use MailVotech\LeadBundle\Entity\LeadList;
use MailVotech\PageBundle\Entity\Page;
use MailVotech\PointBundle\Entity\Point;
use MailVotech\PointBundle\Entity\Trigger;
use MailVotech\SmsBundle\Entity\Sms;
use MailVotech\StageBundle\Entity\Stage;
use MailVotechPlugin\MailVotechFocusBundle\Entity\Focus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ApiSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ApiInitializeEvent::class=> ['onApiInitializeEvent', 0],
        ];
    }

    public function onApiInitializeEvent(ApiInitializeEvent $event): void
    {
        if (!in_array($event->getEntityClass(), [
            Asset::class,
            Campaign::class,
            Message::class,
            DynamicContent::class,
            Email::class,
            Form::class,
            Company::class,
            LeadList::class,
            Page::class,
            Point::class,
            Trigger::class,
            Sms::class,
            Stage::class,
            Focus::class,
        ])) {
            return;
        }

        $event->addSerializerGroup('projectList');
        $event->addExclusionStrategy(new FieldInclusionStrategy(['id', 'name'], 1, 'projects'));
    }
}
