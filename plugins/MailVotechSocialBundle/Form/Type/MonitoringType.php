<?php

namespace MailVotechPlugin\MailVotechSocialBundle\Form\Type;

use MailVotech\CategoryBundle\Form\Type\CategoryListType;
use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\PublishDownDateType;
use MailVotech\CoreBundle\Form\Type\PublishUpDateType;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\LeadBundle\Form\Type\LeadListType;
use MailVotechPlugin\MailVotechSocialBundle\Entity\Monitoring;
use MailVotechPlugin\MailVotechSocialBundle\Model\MonitoringModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<mixed>>
 */
final class MonitoringType extends AbstractType
{
    public function __construct(
        private readonly MonitoringModel $monitoringModel,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'html']));

        $builder->add('title', TextType::class, [
            'label'      => 'mailvotech.core.name',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control'],
        ]);

        $builder->add('description', TextareaType::class, [
            'label'      => 'mailvotech.core.description',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => ['class' => 'form-control editor'],
            'required'   => false,
        ]);

        $builder->add('isPublished', YesNoButtonGroupType::class);
        $builder->add('publishUp', PublishUpDateType::class);
        $builder->add('publishDown', PublishDownDateType::class);
        $builder->add('networkType', ChoiceType::class, [
            'label'      => 'mailvotech.social.monitoring.type.list',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'    => 'form-control',
                'onchange' => 'MailVotech.getNetworkFormAction(this)',
            ],
            'choices'           => array_flip((array) $options['networkTypes']), // passed from the controller
            'placeholder'       => 'mailvotech.core.form.chooseone',
        ]);

        // if we have a network type value add in the form
        if (!empty($options['networkType']) && array_key_exists($options['networkType'], $options['networkTypes'])) {
            // get the values from the entity function
            $properties = $options['data']->getProperties();

            $formType = $this->monitoringModel->getFormByType($options['networkType']);

            $builder->add('properties', $formType,
                [
                    'label' => false,
                    'data'  => $properties,
                ]
            );
        }

        $builder->add(
            'lists',
            LeadListType::class,
            [
                'label'      => 'mailvotech.lead.lead.events.addtolists',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'multiple' => true,
                'expanded' => false,
            ]
        );

        // add category
        $builder->add('category', CategoryListType::class, [
            'bundle' => 'plugin:mailvotechSocial',
        ]);

        $builder->add('buttons', FormButtonsType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Monitoring::class,
        ]);

        // allow network types to be sent through - list
        $resolver->setRequired(['networkTypes']);

        // allow the specific network type - single
        $resolver->setDefined(['networkType']);
    }
}
