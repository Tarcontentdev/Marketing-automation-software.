<?php

declare(strict_types=1);

namespace MailVotech\DynamicContentBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class DynamicContentDecisionType extends DynamicContentSendType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'dwc_slot_name',
            TextType::class,
            [
                'label'      => 'mailvotech.dynamicContent.send.slot_name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.dynamicContent.send.slot_name.tooltip',
                ],
                'required'    => true,
                'constraints' => [
                    new NotBlank(message: 'mailvotech.core.value.required'),
                ],
            ]
        );

        parent::buildForm($builder, $options);

        $builder->add(
            'dynamicContent',
            DynamicContentListType::class,
            [
                'label'      => 'mailvotech.dynamicContent.send.selectDynamicContents.default',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'tooltip'  => 'mailvotech.dynamicContent.choose.dynamicContents',
                    'onchange' => 'MailVotech.disabledDynamicContentAction()',
                ],
                'where'       => 'e.isCampaignBased = 1', // do not show dwc with filters
                'multiple'    => false,
                'required'    => true,
                'constraints' => [
                    new NotBlank(message: 'mailvotech.core.value.required'),
                ],
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'dwcdecision_list';
    }
}
