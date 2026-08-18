<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Form\Type;

use MailVotech\UserBundle\Form\Type\UserListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<mixed>
 */
final class FormSubmitActionUserEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('useremail',
            EmailSendType::class,
            [
                'label' => 'mailvotech.email.emails',
                'attr'  => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.email.choose.emails_descr',
                ],
                'update_select' => 'formaction_properties_useremail_email',
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
                'required'    => true,
                'constraints' => new NotBlank(
                    message: 'mailvotech.core.value.required'
                ),
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'email_submitaction_useremail';
    }
}
