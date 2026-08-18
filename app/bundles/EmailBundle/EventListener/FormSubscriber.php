<?php

namespace MailVotech\EmailBundle\EventListener;

use Doctrine\ORM\ORMException;
use MailVotech\EmailBundle\Form\Type\EmailSendType;
use MailVotech\EmailBundle\Form\Type\FormSubmitActionUserEmailType;
use MailVotech\EmailBundle\Model\EmailModel;
use MailVotech\FormBundle\Event\FormBuilderEvent;
use MailVotech\FormBundle\Event\SubmissionEvent;
use MailVotech\FormBundle\FormEvents;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Tracker\ContactTracker;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class FormSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EmailModel $emailModel,
        private ContactTracker $contactTracker,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::FORM_ON_BUILD            => ['onFormBuilder', 0],
            FormEvents::ON_EXECUTE_SUBMIT_ACTION => [
                ['onFormSubmitActionSendEmail', 0],
            ],
        ];
    }

    /**
     * Add a send email actions to available form submit actions.
     */
    public function onFormBuilder(FormBuilderEvent $event): void
    {
        $event->addSubmitAction('email.send.user', [
            'group'       => 'mailvotech.email.actions',
            'label'       => 'mailvotech.email.form.action.sendemail.admin',
            'description' => 'mailvotech.email.form.action.sendemail.admin.descr',
            'formType'    => FormSubmitActionUserEmailType::class,
            'formTheme'   => '@MailVotechEmail/FormTheme/FormAction/_formaction_properties_useremail_row.html.twig',
            'eventName'   => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
            'template'    => '@MailVotechEmail/Action/email.html.twig',
        ]);

        $event->addSubmitAction('email.send.lead', [
            'group'           => 'mailvotech.email.actions',
            'label'           => 'mailvotech.email.form.action.sendemail.lead',
            'description'     => 'mailvotech.email.form.action.sendemail.lead.descr',
            'formType'        => EmailSendType::class,
            'formTypeOptions' => ['update_select' => 'formaction_properties_email'],
            'formTheme'       => '@MailVotechEmail/FormTheme/EmailSendList/emailsend_list_row.html.twig',
            'eventName'       => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
            'template'        => '@MailVotechEmail/Action/email.html.twig',
        ]);
    }

    /**
     * @throws ORMException
     */
    public function onFormSubmitActionSendEmail(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('email.send.user') && false === $event->checkContext('email.send.lead')) {
            return;
        }

        $properties = $event->getAction()->getProperties();
        $emailId    = isset($properties['useremail']) ? (int) $properties['useremail']['email'] : (int) $properties['email'];
        $email      = $this->emailModel->getEntity($emailId);

        if (null === $email || false === $email->isPublished()) {
            return;
        }

        $currentLead = $this->getCurrentLead($event->getActionFeedback());

        if (isset($properties['user_id']) && $properties['user_id']) {
            $this->emailModel->sendEmailToUser($email, $properties['user_id'], $currentLead, $event->getTokens());
        } elseif (isset($currentLead['email'])) {
            $this->emailModel->sendEmail($email, $currentLead, [
                'source'    => ['form', $event->getAction()->getForm()->getId()],
                'tokens'    => $event->getTokens(),
                'ignoreDNC' => true,
            ]);
        }
    }

    private function getCurrentLead(array $feedback): ?array
    {
        // Deal with Lead email
        if (!empty($feedback['lead.create']['lead'])) {
            // the lead was just created via the lead.create action
            $currentLead = $feedback['lead.create']['lead'];
        } else {
            $currentLead = $this->contactTracker->getContact();
        }

        if ($currentLead instanceof Lead) {
            $currentLead = $currentLead->getProfileFields();
        }

        return $currentLead;
    }
}
