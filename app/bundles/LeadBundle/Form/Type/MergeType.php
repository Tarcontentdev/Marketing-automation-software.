<?php

namespace MailVotech\LeadBundle\Form\Type;

use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<mixed>
 */
final class MergeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'lead_to_merge',
            ChoiceType::class,
            [
                'choices'           => $options['leads'],
                'label'             => 'mailvotech.lead.merge.select',
                'label_attr'        => ['class' => 'control-label'],
                'multiple'          => false,
                'placeholder'       => '',
                'attr'              => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.lead.merge.select.modal.tooltip',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'mailvotech.core.value.required'
                    ),
                ],
            ]
        );

        $builder->add(
            'buttons',
            FormButtonsType::class,
            [
                'apply_text' => false,
                'save_text'  => 'mailvotech.lead.merge',
                'save_icon'  => 'ri-user-6-line',
            ]
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['leads']);
    }

    public function getBlockPrefix(): string
    {
        return 'lead_merge';
    }
}
