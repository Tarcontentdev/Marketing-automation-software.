<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Form;

use MailVotech\EmailBundle\Validator\MultipleEmailsValid;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

trait ToBcBccFieldsTrait
{
    protected function addToBcBccFields(FormBuilderInterface $builder): void
    {
        $multipleEmailConstraint = new MultipleEmailsValid();

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
                'constraints' => $multipleEmailConstraint,
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
                'constraints' => $multipleEmailConstraint,
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
                'constraints' => $multipleEmailConstraint,
            ]
        );
    }
}
