<?php

declare(strict_types=1);

namespace MailVotech\PointBundle\Form\Type;

use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\EventListener\FormExitSubscriber;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\PointBundle\Entity\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<GroupType>
 */
final class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('point.group', $options));

        $builder->add('name', TextType::class, [
            'label'      => 'mailvotech.core.name',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control'],
        ]);

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

        $data = false;
        if (!empty($options['data']) && $options['data'] instanceof Group) {
            $data = $options['data']->isPublished(false);
        }
        $builder->add('isPublished', YesNoButtonGroupType::class, [
            'label'     => 'mailvotech.core.form.available',
            'data'      => $data,
        ]);

        $builder->add('buttons', FormButtonsType::class);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Group::class,
            ]
        );
    }
}
