<?php

namespace MailVotech\NotificationBundle\Form\Type;

use MailVotech\CategoryBundle\Form\Type\CategoryListType;
use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\EventListener\FormExitSubscriber;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\PublishDownDateType;
use MailVotech\CoreBundle\Form\Type\PublishUpDateType;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\EmailBundle\Form\Type\EmailUtmTagsType;
use MailVotech\NotificationBundle\Entity\Notification;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Notification>
 */
final class NotificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['content' => 'html', 'customHtml' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('notification.notification', $options));

        $builder->add(
            'name',
            TextType::class,
            [
                'label'      => 'mailvotech.notification.form.internal.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
            ]
        );

        $builder->add(
            'description',
            TextareaType::class,
            [
                'label'      => 'mailvotech.notification.form.internal.description',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
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
            'heading',
            TextType::class,
            [
                'label'      => 'mailvotech.notification.form.heading',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => true,
            ]
        );

        $builder->add(
            'message',
            TextareaType::class,
            [
                'label'      => 'mailvotech.notification.form.message',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                    'rows'  => 6,
                ],
                'required' => true,
            ]
        );

        $builder->add(
            'url',
            UrlType::class,
            [
                'label'      => 'mailvotech.notification.form.url',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.notification.form.url.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'button',
            TextType::class,
            [
                'label'      => 'mailvotech.notification.form.button',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.notification.form.button.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add('isPublished', YesNoButtonGroupType::class);
        $builder->add('publishUp', PublishUpDateType::class);
        $builder->add('publishDown', PublishDownDateType::class);

        // add category
        $builder->add(
            'category',
            CategoryListType::class,
            [
                'bundle' => 'notification',
            ]
        );

        $builder->add(
            'language',
            LocaleType::class,
            [
                'label'      => 'mailvotech.core.language',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'required' => false,
            ]
        );

        $builder->add('buttons', FormButtonsType::class);

        if (!empty($options['update_select'])) {
            $builder->add(
                'buttons',
                FormButtonsType::class,
                [
                    'apply_text' => false,
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
                FormButtonsType::class
            );
        }

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Notification::class,
            ]
        );

        $resolver->setDefined(['update_select']);
    }
}
