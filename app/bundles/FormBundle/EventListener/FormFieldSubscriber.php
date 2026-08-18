<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\EventListener;

use MailVotech\FormBundle\Event\FormFieldEvent;
use MailVotech\FormBundle\FormEvents;
use MailVotech\FormBundle\Model\FieldModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class FormFieldSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FieldModel $fieldModel,
    ) {
    }

    /**
     * @return mixed[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::FIELD_POST_DELETE => ['onFieldPostDelete', 0],
        ];
    }

    public function onFieldPostDelete(FormFieldEvent $event): void
    {
        $field = $event->getField();

        if (null !== $field->deletedId) {
            $this->fieldModel->removeFieldColumn($field);
        }
    }
}
