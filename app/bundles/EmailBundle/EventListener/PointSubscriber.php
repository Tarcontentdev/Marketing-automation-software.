<?php

namespace MailVotech\EmailBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use MailVotech\EmailBundle\EmailEvents;
use MailVotech\EmailBundle\Event\EmailOpenEvent;
use MailVotech\EmailBundle\Event\EmailSendEvent;
use MailVotech\EmailBundle\Form\Type\EmailOpenType;
use MailVotech\EmailBundle\Form\Type\EmailSendType;
use MailVotech\EmailBundle\Form\Type\EmailToUserType;
use MailVotech\EmailBundle\Helper\PointEventHelper;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\PointBundle\Event\PointBuilderEvent;
use MailVotech\PointBundle\Event\TriggerBuilderEvent;
use MailVotech\PointBundle\Model\PointModel;
use MailVotech\PointBundle\PointEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class PointSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PointModel $pointModel,
        private EntityManagerInterface $entityManager,
        private PointEventHelper $pointEventHelper,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PointEvents::POINT_ON_BUILD   => ['onPointBuild', 0],
            PointEvents::TRIGGER_ON_BUILD => ['onTriggerBuild', 0],
            EmailEvents::EMAIL_ON_OPEN    => ['onEmailOpen', 0],
            EmailEvents::EMAIL_ON_SEND    => ['onEmailSend', 0],
        ];
    }

    public function onPointBuild(PointBuilderEvent $event): void
    {
        $action = [
            'group'    => 'mailvotech.email.actions',
            'label'    => 'mailvotech.email.point.action.open',
            'callback' => [PointEventHelper::class, 'validateEmail'],
            'formType' => EmailOpenType::class,
        ];

        $event->addAction('email.open', $action);

        $action = [
            'group'    => 'mailvotech.email.actions',
            'label'    => 'mailvotech.email.point.action.send',
            'callback' => [PointEventHelper::class, 'validateEmail'],
            'formType' => EmailOpenType::class,
        ];

        $event->addAction('email.send', $action);
    }

    public function onTriggerBuild(TriggerBuilderEvent $event): void
    {
        $sendEvent = [
            'group'           => 'mailvotech.email.point.trigger',
            'label'           => 'mailvotech.email.point.trigger.sendemail',
            'callback'        => [$this->pointEventHelper, 'sendEmail'],
            'formType'        => EmailSendType::class,
            'formTypeOptions' => ['update_select' => 'pointtriggerevent_properties_email'],
            'formTheme'       => '@MailVotechEmail/FormTheme/EmailSendList/emailsend_list_row.html.twig',
        ];

        $event->addEvent('email.send', $sendEvent);

        $sendToOwnerEvent = [
            'group'           => 'mailvotech.email.point.trigger',
            'label'           => 'mailvotech.email.point.trigger.send_email_to_user',
            'formType'        => EmailToUserType::class,
            'formTypeOptions' => ['update_select' => 'pointtriggerevent_properties_useremail_email'],
            'formTheme'       => '@MailVotechEmail/FormTheme/EmailSendList/email_to_user_row.html.twig',
            'eventName'       => EmailEvents::ON_SENT_EMAIL_TO_USER,
        ];

        $event->addEvent('email.send_to_user', $sendToOwnerEvent);
    }

    /**
     * Trigger point actions for email open.
     */
    public function onEmailOpen(EmailOpenEvent $event): void
    {
        $this->pointModel->triggerAction('email.open', $event->getEmail());
    }

    /**
     * Trigger point actions for email send.
     */
    public function onEmailSend(EmailSendEvent $event): void
    {
        $leadArray = $event->getLead();
        if ($leadArray && is_array($leadArray) && !empty($leadArray['id'])) {
            $lead = $this->entityManager->getReference(Lead::class, $leadArray['id']);
        } else {
            return;
        }

        try {
            $this->pointModel->triggerAction('email.send', $event->getEmail(), null, $lead, true);
        } catch (EntityNotFoundException) {
            // Contact was deleted in the mean time, skip point triggering
            return;
        }
    }
}
