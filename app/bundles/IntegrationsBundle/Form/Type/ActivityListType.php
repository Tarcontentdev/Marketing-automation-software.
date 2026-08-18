<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Form\Type;

use MailVotech\LeadBundle\Model\LeadModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class ActivityListType extends AbstractType
{
    public function __construct(
        private readonly LeadModel $leadModel,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'choices'    => array_flip($this->leadModel->getEngagementTypes()),
                'label'      => 'mailvotech.integration.feature.push_activity.included_events',
                'label_attr' => [
                    'class'       => 'control-label',
                    'tooltip'     => 'mailvotech.integration.feature.push_activity.included_events.tooltip',
                ],
                'multiple'   => true,
                'required'   => false,
            ]
        );
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
