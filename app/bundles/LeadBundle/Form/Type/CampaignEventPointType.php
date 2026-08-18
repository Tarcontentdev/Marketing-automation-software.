<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Form\Type;

use MailVotech\LeadBundle\Provider\TypeOperatorProviderInterface;
use MailVotech\LeadBundle\Segment\OperatorOptions;
use MailVotech\PointBundle\Form\Type\GroupListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<CampaignEventPointType>
 */
final class CampaignEventPointType extends AbstractType
{
    public function __construct(
        private readonly TypeOperatorProviderInterface $typeOperatorProvider,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'operator',
            ChoiceType::class,
            [
                'label'             => 'mailvotech.lead.campaign.event.point_operator',
                'multiple'          => false,
                'choices'           => $this->typeOperatorProvider->getOperatorsIncluding([
                    OperatorOptions::EQUAL_TO,
                    OperatorOptions::NOT_EQUAL_TO,
                    OperatorOptions::GREATER_THAN,
                    OperatorOptions::LESS_THAN,
                    OperatorOptions::GREATER_THAN_OR_EQUAL,
                    OperatorOptions::LESS_THAN_OR_EQUAL,
                ]),
                'required'   => true,
                'label_attr' => ['class' => 'control-label'],
            ]
        );

        $builder->add(
            'score',
            NumberType::class,
            [
                'label'      => 'mailvotech.lead.campaign.event.point_score',
                'attr'       => ['class' => 'form-control'],
                'label_attr' => ['class' => 'control-label'],
                'scale'      => 0,
                'required'   => true,
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
}
