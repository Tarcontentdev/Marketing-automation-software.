<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechFocusBundle\Form\Type;

use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, mixed>>
 */
final class PropertiesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'bar',
            FocusPropertiesType::class,
            [
                'focus_style' => 'bar',
                'data'        => $options['data']['bar'] ?? [],
            ]
        );

        $builder->add(
            'modal',
            FocusPropertiesType::class,
            [
                'focus_style' => 'modal',
                'data'        => $options['data']['modal'] ?? [],
            ]
        );

        $builder->add(
            'notification',
            FocusPropertiesType::class,
            [
                'focus_style' => 'notification',
                'data'        => $options['data']['notification'] ?? [],
            ]
        );

        $builder->add(
            'page',
            FocusPropertiesType::class,
            [
                'focus_style' => 'page',
                'data'        => $options['data']['page'] ?? [],
            ]
        );

        $builder->add(
            'animate',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.focus.form.animate',
                'data'  => $options['data']['animate'] ?? true,
                'attr'  => [
                    'onchange' => 'MailVotech.focusUpdatePreview()',
                ],
            ]
        );

        $builder->add(
            'link_activation',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.focus.form.activate_for_links',
                'data'  => $options['data']['link_activation'] ?? true,
                'attr'  => [
                    'data-show-on' => '{"focus_properties_when": ["leave"]}',
                ],
            ]
        );

        $builder->add(
            'colors',
            ColorType::class,
            [
                'label' => false,
            ]
        );

        $builder->add(
            'content',
            ContentType::class,
            [
                'label' => false,
            ]
        );

        $builder->add(
            'when',
            ChoiceType::class,
            [
                'choices'           => [
                    'mailvotech.focus.form.when.immediately'   => 'immediately',
                    'mailvotech.focus.form.when.scroll_slight' => 'scroll_slight',
                    'mailvotech.focus.form.when.scroll_middle' => 'scroll_middle',
                    'mailvotech.focus.form.when.scroll_bottom' => 'scroll_bottom',
                    'mailvotech.focus.form.when.leave'         => 'leave',
                ],
                'label'       => 'mailvotech.focus.form.when',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'expanded'    => false,
                'multiple'    => false,
                'required'    => false,
                'placeholder' => false,
            ]
        );

        $builder->add(
            'timeout',
            TextType::class,
            [
                'label'      => 'mailvotech.focus.form.timeout',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'          => 'form-control',
                    'postaddon_text' => 'sec',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'frequency',
            ChoiceType::class,
            [
                'choices'           => [
                    'mailvotech.focus.form.frequency.everypage' => 'everypage',
                    'mailvotech.focus.form.frequency.once'      => 'once',
                    'mailvotech.focus.form.frequency.q2m'       => 'q2min',
                    'mailvotech.focus.form.frequency.q15m'      => 'q15min',
                    'mailvotech.focus.form.frequency.hourly'    => 'hourly',
                    'mailvotech.focus.form.frequency.daily'     => 'daily',
                ],
                'label'       => 'mailvotech.focus.form.frequency',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control', 'tooltip' => 'mailvotech.focus.form.frequency.help'],
                'expanded'    => false,
                'multiple'    => false,
                'required'    => false,
                'placeholder' => false,
            ]
        );

        $builder->add(
            'stop_after_conversion',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.focus.form.engage_after_conversion',
                'data'  => $options['data']['stop_after_conversion'] ?? true,
                'attr'  => [
                    'tooltip' => 'mailvotech.focus.form.engage_after_conversion.tooltip',
                ],
            ]
        );

        $builder->add(
            'stop_after_close',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.focus.form.stop_after_close',
                'data'  => $options['data']['stop_after_close'] ?? false,
                'attr'  => [
                    'tooltip' => 'mailvotech.focus.form.stop_after_close.tooltip',
                ],
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'focus_entity_properties';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'label' => false,
            ]
        );
    }
}
