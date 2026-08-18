<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Field\EventListener;

use MailVotech\LeadBundle\Event\LeadFieldEvent;
use MailVotech\LeadBundle\LeadEvents;
use MailVotech\LeadBundle\Model\FieldModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class LeadFieldSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FieldModel $fieldModel,
        private RouterInterface $router,
        private TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LeadEvents::FIELD_PRE_DELETE    => ['onFieldPreDelete', 0],
        ];
    }

    /**
     * Check if Custom field is used in any segment before delete operation.
     */
    public function onFieldPreDelete(LeadFieldEvent $event): void
    {
        $field    = $event->getField();
        $segments = $this->fieldModel->getFieldSegments($field);

        if (count($segments)) {
            $url = $this->router->generate(
                'mailvotech_segment_index',
                ['search' => 'filters_field:'.$field->getAlias()]
            );
            $messageVars = [
                '%name%' => $field->getName(),
                '%id%'   => $field->getId(),
                '%url%'  => $url,
            ];
            $message = $this->translator->trans('mailvotech.core.notice.used.field', $messageVars, 'flashes');
            $event->addDependencyError($message);
        }
    }
}
