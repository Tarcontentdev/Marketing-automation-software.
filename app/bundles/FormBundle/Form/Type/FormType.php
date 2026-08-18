<?php

namespace MailVotech\FormBundle\Form\Type;

use MailVotech\CategoryBundle\Form\Type\CategoryListType;
use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\EventListener\FormExitSubscriber;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\PublishDownDateType;
use MailVotech\CoreBundle\Form\Type\PublishUpDateType;
use MailVotech\CoreBundle\Form\Type\ThemeListType;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\CoreBundle\Helper\LanguageHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\FormBundle\Entity\Form;
use MailVotech\ProjectBundle\Form\Type\ProjectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Form>
 */
final class FormType extends AbstractType
{
    public function __construct(
        private readonly CorePermissions $security,
        private readonly LanguageHelper $langHelper,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('form.form', $options));

        // details
        $builder->add('name', TextType::class, [
            'label'      => 'mailvotech.core.name',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control'],
        ]);

        $builder->add(
            'language',
            ChoiceType::class,
            [
                'choices'           => $this->langHelper->getLanguageChoices(),
                'label'             => 'mailvotech.core.config.form.locale',
                'required'          => false,
                'attr'              => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.form.form.locale.tooltip',
                ],
                'placeholder'       => '',
            ]
        );

        $builder->add('formAttributes', TextType::class, [
            'label'      => 'mailvotech.form.field.form.form_attr',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'   => 'form-control',
                'tooltip' => 'mailvotech.form.field.form.form_attr.tooltip',
            ],
            'required'   => false,
        ]);

        $builder->add('description', TextareaType::class, [
            'label'      => 'mailvotech.core.description',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control editor'],
            'required'   => false,
        ]);

        // add category
        $builder->add(
            'category',
            CategoryListType::class,
            [
                'bundle' => 'form',
            ]
        );

        $builder->add('projects', ProjectType::class);

        $builder->add('template', ThemeListType::class, [
            'include_code_mode' => false,
            'feature'           => 'form',
            'placeholder'       => ' ',
            'attr'              => [
                'class'   => 'form-control',
                'tooltip' => 'mailvotech.form.form.template.help',
            ],
        ]);

        if (!empty($options['data']) && $options['data']->getId()) {
            $readonly = !$this->security->hasEntityAccess(
                'form:forms:publishown',
                'form:forms:publishother',
                $options['data']->getCreatedBy()
            );

            $data = $options['data']->isPublished(false);
        } elseif (!$this->security->isGranted('form:forms:publishown')) {
            $readonly = true;
            $data     = false;
        } else {
            $readonly = false;
            $data     = true;
        }

        $builder->add('isPublished', YesNoButtonGroupType::class, [
            'label' => 'mailvotech.core.form.available',
            'data'  => $data,
            'attr'  => [
                'readonly' => $readonly,
            ],
        ]);

        $builder->add('inKioskMode', YesNoButtonGroupType::class, [
            'label'     => 'mailvotech.form.form.kioskmode',
            'attr'      => [
                'tooltip' => 'mailvotech.form.form.kioskmode.tooltip',
            ],
            'yes_label' => 'mailvotech.form.form.kioskmode.yes',
            'no_label'  => 'mailvotech.form.form.kioskmode.no',
        ]);

        $builder->add(
            'noIndex',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.form.form.no_index',
                'data'  => $options['data']->getNoIndex(),
            ]
        );

        $builder->add(
            'progressiveProfilingLimit',
            TextType::class,
            [
                'label' => 'mailvotech.form.form.progressive_profiling_limit.max_fields',
                'attr'  => [
                    'class'       => 'form-control form-control-narrow',
                    'tooltip'     => 'mailvotech.form.form.progressive_profiling_limit.max_fields.tooltip',
                    'placeholder' => 'mailvotech.form.form.progressive_profiling_limit_unlimited',
                ],
                'data'  => $options['data']->getProgressiveProfilingLimit() ?: '',
            ]
        );

        // Render style for new form by default
        if (null === $options['data']->getId()) {
            $options['data']->setRenderStyle(true);
        }

        $builder->add('renderStyle', YesNoButtonGroupType::class, [
            'label'      => 'mailvotech.form.form.renderstyle',
            'data'       => $options['data']->getRenderStyle() ?? true,
            'attr'       => [
                'tooltip' => 'mailvotech.form.form.renderstyle.tooltip',
            ],
        ]);

        $builder->add('publishUp', PublishUpDateType::class);
        $builder->add('publishDown', PublishDownDateType::class);

        $builder->add('submissionLimit', IntegerType::class, [
            'label'      => 'mailvotech.form.submission.limit',
            'attr'       => [
                'class' => 'form-control form-control-narrow',
            ],
            'required'   => false,
        ]);

        $builder->add('submissionLimitMessage', TextareaType::class, [
            'label'      => 'mailvotech.form.submission.limit_message',
            'attr'       => [
                'class' => 'form-control',
                'rows'  => 3,
            ],
            'required'   => false,
        ]);

        $builder->add('postAction', ChoiceType::class, [
            'choices' => [
                'mailvotech.form.form.postaction.message'  => 'message',
                'mailvotech.form.form.postaction.redirect' => 'redirect',
                'mailvotech.form.form.postaction.return'   => 'return',
                'mailvotech.form.form.postaction.hideform' => 'hideform',
            ],
            'label'             => 'mailvotech.form.form.postaction',
            'label_attr'        => ['class' => 'control-label'],
            'attr'              => [
                'class'    => 'form-control',
                'onchange' => 'MailVotech.onPostSubmitActionChange(this.value);',
            ],
            'required'    => false,
            'placeholder' => false,
        ]);

        $postAction = (isset($options['data'])) ? $options['data']->getPostAction() : '';
        $required   = in_array($postAction, ['redirect', 'message', 'hideform']);
        $builder->add('postActionProperty', TextType::class, [
            'label'      => 'mailvotech.form.form.postactionproperty',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'         => 'form-control',
                'tooltip'       => 'mailvotech.form.form.postactionproperty.tooltip',
                'data-hide-on'  => '{"mailvotechform_postAction":"return"}',
            ],
            'required'   => $required,
        ]);

        $builder->add('sessionId', HiddenType::class, [
            'mapped' => false,
        ]);

        $builder->add('buttons', FormButtonsType::class);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'        => Form::class,
            'validation_groups' => [
                Form::class,
                'determineValidationGroups',
            ],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'mailvotechform';
    }
}
