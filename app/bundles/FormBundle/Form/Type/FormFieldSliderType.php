<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Form\Type;

use MailVotech\FormBundle\Validator\Constraint\SliderMaxGreaterThanMin;
use MailVotech\FormBundle\Validator\Constraint\SliderStepLessThanMax;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Range;

/**
 * @extends AbstractType<mixed>
 */
final class FormFieldSliderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('min', ConstrainedIntegerType::class, [
            'label'      => 'mailvotech.form.field.form.slider_min',
            'label_attr' => ['class' => 'control-label'],
            'required'   => false,
            'attr'       => ['class' => 'form-control'],
            'data'       => $options['data']['min'] ?? 0,
        ]);

        $builder->add('max', ConstrainedIntegerType::class, [
            'label'       => 'mailvotech.form.field.form.slider_max',
            'label_attr'  => ['class' => 'control-label'],
            'required'    => false,
            'attr'        => ['class' => 'form-control'],
            'data'        => $options['data']['max'] ?? 100,
            'constraints' => [
                new SliderMaxGreaterThanMin(message: 'mailvotech.form.field.form.slider_max_gt_min_error'),
            ],
        ]);

        $builder->add('step', ConstrainedIntegerType::class, [
            'label'       => 'mailvotech.form.field.form.slider_step',
            'label_attr'  => ['class' => 'control-label'],
            'required'    => false,
            'attr'        => ['class' => 'form-control'],
            'data'        => $options['data']['step'] ?? 1,
            'constraints' => [
                new Range(minMessage: 'mailvotech.form.field.form.slider_step_min_error', min: 1),
                new SliderStepLessThanMax(message: 'mailvotech.form.field.form.slider_step_lt_max_error'),
            ],
        ]);
    }
}
