<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\EventListener;

use MailVotech\FormBundle\Event\SubmissionEvent;
use MailVotech\FormBundle\Form\Type\PointActionFormSubmitType;
use MailVotech\FormBundle\FormEvents;
use MailVotech\FormBundle\Helper\PointActionHelper;
use MailVotech\PointBundle\Event\PointBuilderEvent;
use MailVotech\PointBundle\Model\PointModel;
use MailVotech\PointBundle\PointEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class PointSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PointModel $pointModel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PointEvents::POINT_ON_BUILD => ['onPointBuild', 0],
            FormEvents::FORM_ON_SUBMIT  => ['onFormSubmit', 0],
        ];
    }

    public function onPointBuild(PointBuilderEvent $event): void
    {
        $action = [
            'group'       => 'mailvotech.form.point.action',
            'label'       => 'mailvotech.form.point.action.submit',
            'description' => 'mailvotech.form.point.action.submit_descr',
            'callback'    => [PointActionHelper::class, 'validateFormSubmit'],
            'formType'    => PointActionFormSubmitType::class,
        ];

        $event->addAction('form.submit', $action);
    }

    /**
     * Trigger point actions for form submit.
     */
    public function onFormSubmit(SubmissionEvent $event): void
    {
        $this->pointModel->triggerAction('form.submit', $event->getSubmission());
    }
}
