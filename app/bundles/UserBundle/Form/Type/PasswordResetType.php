<?php

namespace MailVotech\UserBundle\Form\Type;

use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<array<mixed>>
 */
final class PasswordResetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CleanFormSubscriber([]));

        $builder->add(
            'identifier',
            TextType::class,
            [
                'label'      => 'mailvotech.user.auth.form.loginusername',
                'label_attr' => ['class' => 'sr-only'],
                'attr'       => [
                    'class'       => 'form-control',
                    'preaddon'    => 'ri-user-6-fill',
                    'placeholder' => 'mailvotech.user.auth.form.loginusername',
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'mailvotech.user.user.passwordreset.notblank'),
                ],
            ]
        );

        $builder->add(
            'submit',
            SubmitType::class,
            [
                'attr' => [
                    'class' => 'btn btn-lg btn-primary btn-block',
                ],
                'label' => 'mailvotech.user.user.passwordreset.reset',
            ]
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function getBlockPrefix(): string
    {
        return 'passwordreset';
    }
}
