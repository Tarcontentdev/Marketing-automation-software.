<?php

declare(strict_types=1);

namespace MailVotech\StageBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<array<mixed>>
 */
final class StageActionChangeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('stage', StageActionListType::class, [
            'label'      => 'mailvotech.stage.selectstage',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'   => 'form-control',
                'tooltip' => 'mailvotech.stage.choose.stage_descr',
            ],
            'multiple'    => false,
            'required'    => true,
            'constraints' => [
                new NotBlank(
                    message: 'mailvotech.core.value.required'
                ),
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['update_select']);
    }

    public function getBlockPrefix(): string
    {
        return 'stageaction_change';
    }
}
