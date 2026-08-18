<?php

namespace MailVotech\InstallBundle\Configurator\Form;

use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\UserBundle\Form\Validator\Constraints\NotWeak;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<mixed>
 */
final class UserStepType extends AbstractType
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $storedData = $this->requestStack->getSession()->get('mailvotech.installer.user', new \stdClass());

        $builder->add(
            'firstname',
            TextType::class,
            [
                'label'       => 'mailvotech.core.firstname',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'required'    => true,
                'data'        => (!empty($storedData->firstname)) ? $storedData->firstname : '',
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'mailvotech.core.value.required'
                    ),
                ],
            ]
        );

        $builder->add(
            'lastname',
            TextType::class,
            [
                'label'       => 'mailvotech.core.lastname',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'required'    => true,
                'data'        => (!empty($storedData->lastname)) ? $storedData->lastname : '',
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'mailvotech.core.value.required'
                    ),
                ],
            ]
        );

        $builder->add(
            'email',
            EmailType::class,
            [
                'label'      => 'mailvotech.install.form.user.email',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'preaddon' => 'ri-mail-line',
                ],
                'required'    => true,
                'data'        => (!empty($storedData->email)) ? $storedData->email : '',
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'mailvotech.core.value.required'
                    ),
                    new Assert\Email(
                        message: 'mailvotech.core.email.required'
                    ),
                ],
            ]
        );

        $builder->add(
            'username',
            TextType::class,
            [
                'label'      => 'mailvotech.install.form.user.username',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'required'    => true,
                'data'        => (!empty($storedData->username)) ? $storedData->username : '',
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'mailvotech.core.value.required'
                    ),
                ],
            ]
        );

        $builder->add(
            'password',
            PasswordType::class,
            [
                'label'      => 'mailvotech.install.form.user.password',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'tooltip'  => 'mailvotech.user.user.form.help.passwordrequirements',
                    'preaddon' => 'ri-lock-fill',
                ],
                'required'    => true,
                'constraints' => [
                    new Assert\NotBlank(
                        message: 'mailvotech.core.value.required'
                    ),
                    new Assert\Length(min: 6, minMessage: 'mailvotech.install.password.minlength'),
                    new NotWeak(message: 'mailvotech.user.user.password.weak'),
                ],
            ]
        );

        $builder->add(
            'buttons',
            FormButtonsType::class,
            [
                'pre_extra_buttons' => [
                    [
                        'name'  => 'next',
                        'label' => 'mailvotech.install.next.step',
                        'type'  => 'submit',
                        'attr'  => [
                            'class'   => 'btn btn-success pull-right btn-next',
                            'icon'    => 'ri-arrow-right-circle-line',
                            'onclick' => 'MailVotechInstaller.showWaitMessage(event);',
                        ],
                    ],
                ],
                'apply_text'  => '',
                'save_text'   => '',
                'cancel_text' => '',
            ]
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function getBlockPrefix(): string
    {
        return 'install_user_step';
    }
}
