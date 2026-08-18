<?php

namespace MailVotech\UserBundle\Form\Type;

use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\EventListener\FormExitSubscriber;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\UserBundle\Entity\Role;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * @extends AbstractType<RoleType>
 */
final class RoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('user.role', $options));

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

        $builder->add('isAdmin', YesNoButtonGroupType::class, [
            'label' => 'mailvotech.user.role.form.isadmin',
            'attr'  => [
                'onchange' => 'MailVotech.togglePermissionVisibility();',
                'tooltip'  => 'mailvotech.user.role.form.isadmin.tooltip',
            ],
        ]);

        // add a normal text field, but add your transformer to it
        $hidden = ($options['data']->isAdmin()) ? ' hide' : '';

        $builder->add(
            'permissions',
            PermissionsType::class,
            [
                'label'    => 'mailvotech.user.role.permissions',
                'mapped'   => false, // we'll have to manually build the permissions for persisting
                'required' => false,
                'attr'     => [
                    'class' => $hidden,
                ],
                'permissionsConfig' => $options['permissionsConfig'],
            ]
        );

        $builder->add('buttons', FormButtonsType::class);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'        => Role::class,
            'constraints'       => [new Valid()],
            'permissionsConfig' => [],
        ]);
    }
}
