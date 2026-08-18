<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Form\Type;

use MailVotech\PointBundle\Form\Type\GroupListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotEqualTo;

/**
 * @extends AbstractType<mixed>
 */
final class PointActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'points',
            NumberType::class,
            [
                'label'       => 'mailvotech.lead.lead.event.points',
                'attr'        => ['class' => 'form-control'],
                'label_attr'  => ['class' => 'control-label'],
                'scale'       => 0,
                'data'        => $options['data']['points'] ?? 0,
                'constraints' => [
                    new NotEqualTo(value: '0', message: 'mailvotech.core.value.required'),
                ],
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
        return 'leadpoints_action';
    }
}
