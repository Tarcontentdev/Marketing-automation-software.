<?php

namespace MailVotech\LeadBundle\EventListener;

use MailVotech\FormBundle\Entity\Field;
use MailVotech\FormBundle\Event\SubmissionEvent;
use MailVotech\FormBundle\Form\Type\FormFieldFileType;
use MailVotech\FormBundle\FormEvents;
use MailVotech\FormBundle\Helper\FormUploader;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\LeadBundle\Twig\Helper\AvatarHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SetContactAvatarFormSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AvatarHelper $avatarHelper,
        private FormUploader $uploader,
        private LeadModel $leadModel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::FORM_ON_SUBMIT => ['onFormSubmit', 0],
        ];
    }

    public function onFormSubmit(SubmissionEvent $submissionEvent): void
    {
        $fields  = $submissionEvent->getForm()->getFields();
        $contact = $submissionEvent->getLead();
        $results = $submissionEvent->getResults();

        if (!$contact) {
            return;
        }

        /** @var Field $field */
        foreach ($fields as $field) {
            if ('file' === $field->getType()) {
                $properties = $field->getProperties();
                if (empty($properties[FormFieldFileType::PROPERTY_PREFERED_PROFILE_IMAGE])) {
                    break;
                }
                if (empty($results[$field->getAlias()])) {
                    break;
                }
                try {
                    $filePath = $this->uploader->getCompleteFilePath($field, $results[$field->getAlias()]);
                    $this->avatarHelper->createAvatarFromFile($contact, $filePath);
                    $contact->setPreferredProfileImage('custom');
                    $this->leadModel->saveEntity($contact);

                    return;
                } catch (\Exception) {
                }
            }
        }
    }
}
