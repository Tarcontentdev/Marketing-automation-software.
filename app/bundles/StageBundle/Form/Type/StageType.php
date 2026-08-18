<?php

namespace MailVotech\StageBundle\Form\Type;

use MailVotech\CategoryBundle\Form\Type\CategoryListType;
use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\EventListener\FormExitSubscriber;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\PublishDownDateType;
use MailVotech\CoreBundle\Form\Type\PublishUpDateType;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\ProjectBundle\Form\Type\ProjectType;
use MailVotech\StageBundle\Entity\Stage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Stage>
 */
final class StageType extends AbstractType
{
    public function __construct(
        private readonly CorePermissions $security,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('stage', $options));

        $builder->add('description', TextareaType::class, [
            'label'      => 'mailvotech.core.description',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control editor'],
            'required'   => false,
        ]);
        $builder->add('name', TextType::class, [
            'label'      => 'mailvotech.core.name',
            'label_attr' => [
                'class' => 'control-label',
            ], 'attr' => [
                'class' => 'form-control',
            ], ]);
        $builder->add('weight', NumberType::class, [
            'label'      => 'mailvotech.stage.action.weight',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'   => 'form-control',
                'tooltip' => 'mailvotech.stage.action.weight.help',
            ],
            'scale'    => 0,
            'required' => false,
        ]);

        if (!empty($options['data']) && $options['data'] instanceof Stage) {
            $readonly = !$this->security->hasEntityAccess(
                'stage:stages:publishown',
                'stage:stages:publishother',
                $options['data']->getCreatedBy()
            );

            $data = $options['data']->isPublished(false);
        } elseif (!$this->security->isGranted('stage:stages:publishown')) {
            $readonly = true;
            $data     = false;
        } else {
            $readonly = false;
            $data     = true;
        }

        $builder->add('isPublished', YesNoButtonGroupType::class, [
            'data' => $data,
            'attr' => [
                'readonly' => $readonly,
            ],
        ]);

        $builder->add('publishUp', PublishUpDateType::class);
        $builder->add('publishDown', PublishDownDateType::class);

        $builder->add('projects', ProjectType::class);

        $builder->add(
            'category',
            CategoryListType::class,
            [
                'bundle' => 'stage',
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
            'data_class' => Stage::class,
        ]);

        $resolver->setDefined(['stageActions', 'actionType']);
    }
}
