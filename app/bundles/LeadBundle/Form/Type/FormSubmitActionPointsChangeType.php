<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Form\Type;

use MailVotech\PointBundle\Form\Type\GroupListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<mixed>
 */
final class FormSubmitActionPointsChangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'operator',
            ChoiceType::class,
            [
                'label'             => 'mailvotech.lead.lead.submitaction.operator',
                'attr'              => ['class' => 'form-control'],
                'label_attr'        => ['class' => 'control-label'],
                'choices'           => [
                    'mailvotech.lead.lead.submitaction.operator_plus'   => 'plus',
                    'mailvotech.lead.lead.submitaction.operator_minus'  => 'minus',
                    'mailvotech.lead.lead.submitaction.operator_times'  => 'times',
                    'mailvotech.lead.lead.submitaction.operator_divide' => 'divide',
                ],
            ]
        );

        $default = (empty($options['data']['points'])) ? 0 : (int) $options['data']['points'];
        $builder->add(
            'points',
            NumberType::class,
            [
                'label'      => 'mailvotech.lead.lead.submitaction.points',
                'attr'       => ['class' => 'form-control'],
                'label_attr' => ['class' => 'control-label'],
                'scale'      => 0,
                'data'       => $default,
            ]
        );

        $builder->add('group', GroupListType::class, [
            'label'            => 'mailvotech.lead.campaign.event.point_group',
            'label_attr'       => ['class' => 'control-label'],
            'attr'             => [
                'class'    => 'form-control',
                'tooltip'  => 'mailvotech.lead.campaign.event.point_group.help',
            ],
            'required'         => false,
            'by_reference'     => false,
            'return_entity'    => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'lead_submitaction_pointschange';
    }
}
