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
use MailVotech\PointBundle\Entity\Point;
use MailVotech\ProjectBundle\Form\Type\ProjectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Point>
 */
final class PointType extends AbstractType
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
            'type',
            ChoiceType::class,
            [
                'choices'           => $options['pointActions']['choices'],
                'placeholder'       => '',
                'label'             => 'mailvotech.point.form.type',
                'label_attr'        => ['class' => 'control-label'],
                'attr'              => [
                    'class'    => 'form-control',
                    'onchange' => 'MailVotech.getPointActionPropertiesForm(this.value);',
                ],
            ]
        );

        $builder->add(
            'delta',
            NumberType::class,
            [
                'label'      => 'mailvotech.point.action.delta',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.point.action.delta.help',
                ],
                'scale' => 0,
            ]
        );

        $type = (!empty($options['actionType'])) ? $options['actionType'] : $options['data']->getType();

        if ($type && !empty($options['pointActions']['actions'][$type]['formType'])) {
            $formType   = $options['pointActions']['actions'][$type]['formType'];
            $properties = ($options['data']) ? $options['data']->getProperties() : [];
            $builder->add(
                'properties',
                $formType,
                [
                    'label' => false,
                    'data'  => $properties,
                ]
            );
        }

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

        if (!empty($options['data']) && $options['data'] instanceof Point) {
            $readonly = !$this->security->hasEntityAccess(
                'point:points:publishown',
                'point:points:publishother',
                $options['data']->getCreatedBy()
            );

            $data = $options['data']->isPublished(false);
        } elseif (!$this->security->isGranted('point:points:publishown')) {
            $readonly = true;
            $data     = false;
        } else {
            $readonly = false;
            $data     = true;
        }

        $builder->add(
            'isPublished',
            YesNoButtonGroupType::class,
            [
                'data' => $data,
                'attr' => [
                    'readonly' => $readonly,
                ],
            ]
        );

        $builder->add(
            'repeatable',
            YesNoButtonGroupType::class,
            [
                'label'     => 'mailvotech.point.form.repeat',
                'data'      => $options['data']->getRepeatable() ?: false,
                'attr'      => [
                    'tooltip' => 'mailvotech.point.form.repeat.help',
                ],
                'yes_label' => 'mailvotech.point.form.repeat.yes',
                'no_label'  => 'mailvotech.point.form.repeat.no',
            ]
        );

        $builder->add('publishUp', PublishUpDateType::class);
        $builder->add('publishDown', PublishDownDateType::class);

        $builder->add(
            'category',
            CategoryListType::class,
            [
                'bundle' => 'point',
            ]
        );

        $builder->add('projects', ProjectType::class);

        $builder->add('buttons', FormButtonsType::class);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Point::class]);
        $resolver->setRequired(['pointActions']);
        $resolver->setDefined(['actionType']);
    }
}
