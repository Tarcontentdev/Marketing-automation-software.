<?php

namespace MailVotech\InstallBundle\Configurator\Form;

use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\InstallBundle\Configurator\Step\DoctrineStep;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Choice;

/**
 * Doctrine Form Type.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 *
 * @note   This class is based on Sensio\Bundle\DistributionBundle\Configurator\Form\DoctrineStepType
 *
 * @extends AbstractType<mixed>
 */
final class DoctrineStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'driver',
            ChoiceType::class,
            [
                'choices'           => array_flip(DoctrineStep::getDrivers()),
                'expanded'          => false,
                'multiple'          => false,
                'label'             => 'mailvotech.install.form.database.driver',
                'label_attr'        => ['class' => 'control-label'],
                'placeholder'       => false,
                'required'          => true,
                'attr'              => [
                    'class' => 'form-control',
                ],
                'constraints'       => [
                    new Choice(
                        callback: '\MailVotech\InstallBundle\Configurator\Step\DoctrineStep::getDriverKeys'
                    ),
                ],
            ]
        );

        $builder->add(
            'host',
            TextType::class,
            [
                'label'      => 'mailvotech.install.form.database.host',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => true,
            ]
        );

        $builder->add(
            'port',
            TextType::class,
            [
                'label'      => 'mailvotech.install.form.database.port',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => false,
            ]
        );

        $builder->add(
            'name',
            TextType::class,
            [
                'label'      => 'mailvotech.install.form.database.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => true,
            ]
        );

        $builder->add(
            'table_prefix',
            TextType::class,
            [
                'label'      => 'mailvotech.install.form.database.table.prefix',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => false,
            ]
        );

        $builder->add(
            'user',
            TextType::class,
            [
                'label'      => 'mailvotech.install.form.database.user',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => true,
            ]
        );

        $builder->add(
            'password',
            PasswordType::class,
            [
                'label'      => 'mailvotech.install.form.database.password',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'preaddon' => 'ri-lock-fill',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'backup_tables',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.install.form.existing_tables',
                'attr'  => [
                    'tooltip'  => 'mailvotech.install.form.existing_tables_descr',
                    'onchange' => 'MailVotechInstaller.toggleBackupPrefix();',
                ],
            ]
        );

        $builder->add(
            'backup_prefix',
            TextType::class,
            [
                'label'      => 'mailvotech.install.form.backup_prefix',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'required' => false,
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
        return 'install_doctrine_step';
    }
}
