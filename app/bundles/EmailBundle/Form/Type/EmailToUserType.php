<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Form\Type;

use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\EmailBundle\Validator\EmailOrEmailTokenList;
use MailVotech\UserBundle\Form\Type\UserListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class EmailToUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('useremail',
            EmailSendType::class, [
                'label' => 'mailvotech.email.emails',
                'attr'  => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.email.choose.emails_descr',
                    'email'   => isset($options['data']) && isset($options['data']['useremail']) && isset($options['data']['useremail']['email']) ? $options['data']['useremail']['email'] : null,
                ],
                'update_select' => empty($options['update_select']) ? 'formaction_properties_useremail_email' : $options['update_select'],
            ]
        );

        $builder->add(
            'user_id',
            UserListType::class,
            [
                'label'      => 'mailvotech.email.form.users',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.core.help.autocomplete',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'to_owner',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.form.action.send.email.to.owner',
                'data'  => $options['data']['to_owner'] ?? false,
            ]
        );

        $builder->add(
            'to',
            TextType::class,
            [
                'label'      => 'mailvotech.core.send.email.to',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'placeholder' => 'mailvotech.core.optional',
                    'tooltip'     => 'mailvotech.core.send.email.to.multiple.addresses',
                ],
                'required'    => false,
                'constraints' => new EmailOrEmailTokenList(),
            ]
        );

        $builder->add(
            'cc',
            TextType::class,
            [
                'label'      => 'mailvotech.core.send.email.cc',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'placeholder' => 'mailvotech.core.optional',
                    'tooltip'     => 'mailvotech.core.send.email.to.multiple.addresses',
                ],
                'required'    => false,
                'constraints' => new EmailOrEmailTokenList(),
            ]
        );

        $builder->add(
            'bcc',
            TextType::class,
            [
                'label'      => 'mailvotech.core.send.email.bcc',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'placeholder' => 'mailvotech.core.optional',
                    'tooltip'     => 'mailvotech.core.send.email.to.multiple.addresses',
                ],
                'required'    => false,
                'constraints' => new EmailOrEmailTokenList(),
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
        ]);

        $resolver->setDefined(['update_select']);
    }
}
