<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\EventListener;

use MailVotech\FormBundle\Entity\FieldRepository;
use MailVotech\FormBundle\Model\FormModel;
use MailVotech\LeadBundle\Event\LeadFieldEvent;
use MailVotech\LeadBundle\LeadEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CustomFieldSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FieldRepository $fieldRepository,
        private FormModel $formModel,
    ) {
    }

    /**
     * @return mixed[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::FIELD_POST_SAVE => ['regenerateFormCache', 0],
        ];
    }

    /**
     * If a custom (contact) field options change then we need to regenerate HTML cache of the forms
     * that use that field so that the new options are shown.
     */
    public function regenerateFormCache(LeadFieldEvent $event): void
    {
        $customField = $event->getField();

        if (!in_array($customField->getType(), ['multiselect', 'select'])) {
            return;
        }

        $affectedFormFields = $this->fieldRepository->getFormFieldsMappedToLeadField($customField);

        $clearedFormIds = [];

        foreach ($affectedFormFields as $formField) {
            if (empty($formField->getProperties()['syncList'])) {
                continue;
            }

            $form = $formField->getForm();

            if (in_array($form->getId(), $clearedFormIds)) {
                continue;
            }

            $this->formModel->generateHtml($form);
            $clearedFormIds[] = $form->getId();
        }
    }
}
