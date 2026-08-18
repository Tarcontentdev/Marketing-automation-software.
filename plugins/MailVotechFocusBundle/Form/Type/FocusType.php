<?php

namespace MailVotechPlugin\MailVotechFocusBundle\Form\Type;

use MailVotech\CategoryBundle\Form\Type\CategoryListType;
use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\EventListener\FormExitSubscriber;
use MailVotech\CoreBundle\Form\Type\ButtonGroupType;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\PublishDownDateType;
use MailVotech\CoreBundle\Form\Type\PublishUpDateType;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\EmailBundle\Form\Type\EmailUtmTagsType;
use MailVotech\FormBundle\Form\Type\FormListType;
use MailVotech\ProjectBundle\Form\Type\ProjectType;
use MailVotechPlugin\MailVotechFocusBundle\Entity\Focus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Focus>
 */
final class FocusType extends AbstractType
{
    public function __construct(
        private readonly CorePermissions $security,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['website' => 'url', 'html' => 'html', 'editor' => 'html', 'description' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('focus', $options));

        $builder->add(
            'name',
            TextType::class,
            [
                'label'      => 'mailvotech.core.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
            ]
        );

        $builder->add(
            'description',
            TextareaType::class,
            [
                'label'      => 'mailvotech.core.description',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control editor'],
                'required'   => false,
            ]
        );

        $builder->add(
            'utmTags',
            EmailUtmTagsType::class,
            [
                'label'      => 'mailvotech.email.utm_tags',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.email.utm_tags.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'html_mode',
            ButtonGroupType::class,
            [
                'label'      => 'mailvotech.focus.form.html_mode',
                'label_attr' => ['class' => 'control-label'],
                'data'       => !empty($options['data']->getHtmlMode()) ? $options['data']->getHtmlMode() : 'basic',
                'attr'       => [
                    'class'    => 'form-control',
                    'onchange' => 'MailVotech.focusUpdatePreview()',
                    'tooltip'  => 'mailvotech.focums.html_mode.tooltip',
                ],
                'choices' => [
                    'mailvotech.focus.form.basic'  => 'basic',
                    'mailvotech.focus.form.editor' => 'editor',
                    'mailvotech.focus.form.html'   => 'html',
                ],
            ]
        );

        $builder->add(
            'editor',
            TextareaType::class,
            [
                'label'      => 'mailvotech.focus.form.editor',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control editor editor-basic',
                    'data-show-on' => '{"focus_html_mode_1":"checked"}',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'html',
            TextareaType::class,
            [
                'label'      => 'mailvotech.focus.form.html',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'        => 'form-control',
                    'rows'         => 12,
                    'data-show-on' => '{"focus_html_mode_2":"checked"}',
                    'onchange'     => 'MailVotech.focusUpdatePreview()',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'website',
            UrlType::class,
            [
                'label'      => 'mailvotech.focus.form.website',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.focus.form.website.tooltip',
                ],
                'required' => false,
            ]
        );

        // add category
        $builder->add(
            'category',
            CategoryListType::class,
            [
                'bundle' => 'plugin:focus',
            ]
        );

        $builder->add('projects', ProjectType::class);

        if (!empty($options['data']) && $options['data']->getId()) {
            $readonly = !$this->security->isGranted('focus:items:publish');
            $data     = $options['data']->isPublished(false);
        } elseif (!$this->security->isGranted('focus:items:publish')) {
            $readonly = true;
            $data     = false;
        } else {
            $readonly = false;
            $data     = false;
        }

        $builder->add(
            'isPublished',
            YesNoButtonGroupType::class,
            [
                'data' => $data,
                'attr' => [
                    'readonly' => $readonly,
                ],
            ]
        );

        $builder->add('publishUp', PublishUpDateType::class);
        $builder->add('publishDown', PublishDownDateType::class);
        $builder->add('properties', PropertiesType::class, ['data' => $options['data']->getProperties()]);

        // Will be managed by JS
        $builder->add('type', HiddenType::class);
        $builder->add('style', HiddenType::class);

        $builder->add(
            'form',
            FormListType::class,
            [
                'label'       => 'mailvotech.focus.form.choose_form',
                'multiple'    => false,
                'placeholder' => '',
                'attr'        => [
                    'onchange' => 'MailVotech.focusUpdatePreview()',
                ],
            ]
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }

        $customButtons = [
            [
                'name'  => 'builder',
                'label' => 'mailvotech.core.builder',
                'attr'  => [
                    'class'   => 'btn btn-tertiary btn-dnd btn-nospin',
                    'icon'    => 'ri-layout-line',
                    'onclick' => 'MailVotech.launchFocusBuilder();',
                ],
            ],
        ];

        if (!empty($options['update_select'])) {
            $builder->add(
                'buttons',
                FormButtonsType::class,
                [
                    'apply_text'        => false,
                    'pre_extra_buttons' => $customButtons,
                ]
            );
            $builder->add(
                'updateSelect',
                HiddenType::class,
                [
                    'data'   => $options['update_select'],
                    'mapped' => false,
                ]
            );
        } else {
            $builder->add(
                'buttons',
                FormButtonsType::class,
                [
                    'pre_extra_buttons' => $customButtons,
                ]
            );
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Focus::class,
            ]
        );
        $resolver->setDefined(['update_select']);
    }
}
