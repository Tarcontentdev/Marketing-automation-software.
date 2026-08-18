<?php

namespace MailVotech\PointBundle\Form\Type;

use MailVotech\CategoryBundle\Form\Type\CategoryListType;
use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\EventListener\FormExitSubscriber;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\PublishDownDateType;
use MailVotech\CoreBundle\Form\Type\PublishUpDateType;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\PointBundle\Entity\Trigger;
use MailVotech\ProjectBundle\Form\Type\ProjectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Trigger>
 */
final class TriggerType extends AbstractType
{
    public function __construct(
        private readonly CorePermissions $security,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('point', $options));

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
            'category',
            CategoryListType::class,
            [
                'bundle' => 'point',
            ]
        );

        $builder->add('projects', ProjectType::class);

        $builder->add(
            'points',
            NumberType::class,
            [
                'label'      => 'mailvotech.point.trigger.form.points',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.point.trigger.form.points_descr',
                ],
                'required' => false,
            ]
        );

        $color = $options['data']->getColor();

        $builder->add(
            'color',
            TextType::class,
            [
                'label'      => 'mailvotech.point.trigger.form.color',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'color',
                    'tooltip'     => 'mailvotech.point.trigger.form.color_descr',
                ],
                'required'   => false,
                'data'       => (!empty($color)) ? $color : 'a0acb8',
                'empty_data' => 'a0acb8',
            ]
        );

        $builder->add(
            'group',
            GroupListType::class,
            [
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.point.group.form.group_descr',
                ],
            ]
        );

        $builder->add(
            'triggerExistingLeads',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.point.trigger.form.existingleads',
            ]
        );

        if (!empty($options['data']) && $options['data']->getId()) {
            $readonly = !$this->security->isGranted('point:triggers:publish');
            $data     = $options['data']->isPublished(false);
        } elseif (!$this->security->isGranted('point:triggers:publish')) {
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
                'data'      => $data,
                'attr'      => [
                    'readonly' => $readonly,
                ],
            ]
        );

        $builder->add('publishUp', PublishUpDateType::class);
        $builder->add('publishDown', PublishDownDateType::class);

        $builder->add(
            'sessionId',
            HiddenType::class,
            [
                'mapped' => false,
            ]
        );

        $builder->add('buttons', FormButtonsType::class);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Trigger::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'pointtrigger';
    }
}
