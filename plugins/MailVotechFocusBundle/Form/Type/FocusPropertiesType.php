<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechFocusBundle\Form\Type;

use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, mixed>>
 */
final class FocusPropertiesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];

        // Type specific
        switch ($options['focus_style']) {
            case 'bar':
                $builder->add(
                    'allow_hide',
                    YesNoButtonGroupType::class,
                    [
                        'label' => 'mailvotech.focus.form.bar.allow_hide',
                        'data'  => $options['data']['allow_hide'] ?? true,
                        'attr'  => [
                            'onchange' => 'MailVotech.focusUpdatePreview()',
                        ],
                    ]
                );

                $builder->add(
                    'push_page',
                    YesNoButtonGroupType::class,
                    [
                        'label' => 'mailvotech.focus.form.bar.push_page',
                        'attr'  => [
                            'tooltip'  => 'mailvotech.focus.form.bar.push_page.tooltip',
                            'onchange' => 'MailVotech.focusUpdatePreview()',
                        ],
                        'data' => $options['data']['push_page'] ?? true,
                    ]
                );

                $builder->add(
                    'sticky',
                    YesNoButtonGroupType::class,
                    [
                        'label' => 'mailvotech.focus.form.bar.sticky',
                        'attr'  => [
                            'tooltip'  => 'mailvotech.focus.form.bar.sticky.tooltip',
                            'onchange' => 'MailVotech.focusUpdatePreview()',
                        ],
                        'data' => $options['data']['sticky'] ?? true,
                    ]
                );

                $builder->add(
                    'size',
                    ChoiceType::class,
                    [
                        'choices'           => [
                            'mailvotech.focus.form.bar.size.large'   => 'large',
                            'mailvotech.focus.form.bar.size.regular' => 'regular',
                        ],
                        'label'      => 'mailvotech.focus.form.bar.size',
                        'label_attr' => ['class' => 'control-label'],
                        'attr'       => [
                            'class'    => 'form-control',
                            'onchange' => 'MailVotech.focusUpdatePreview()',
                        ],
                        'required'    => false,
                        'placeholder' => false,
                    ]
                );

                $choices = [
                    'mailvotech.focus.form.placement.top'    => 'top',
                    'mailvotech.focus.form.placement.bottom' => 'bottom',
                ];
                break;
            case 'modal':
                $choices = [
                    'mailvotech.focus.form.placement.top'    => 'top',
                    'mailvotech.focus.form.placement.middle' => 'middle',
                    'mailvotech.focus.form.placement.bottom' => 'bottom',
                ];
                break;
            case 'notification':
                $choices = [
                    'mailvotech.focus.form.placement.top_left'     => 'top_left',
                    'mailvotech.focus.form.placement.top_right'    => 'top_right',
                    'mailvotech.focus.form.placement.bottom_left'  => 'bottom_left',
                    'mailvotech.focus.form.placement.bottom_right' => 'bottom_right',
                ];
                break;
            case 'page':
                break;
        }

        if ([] !== $choices) {
            $builder->add(
                'placement',
                ChoiceType::class,
                [
                    'choices'           => $choices,
                    'label'             => 'mailvotech.focus.form.placement',
                    'label_attr'        => ['class' => 'control-label'],
                    'attr'              => [
                        'class'    => 'form-control',
                        'onchange' => 'MailVotech.focusUpdatePreview()',
                        'tooltip'  => 'mailvotech.focus.form.placement.help',
                    ],
                    'required'    => false,
                    'placeholder' => false,
                ]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['focus_style']);

        $resolver->setDefaults(
            [
                'label' => false,
            ]
        );
    }
}
