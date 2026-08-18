<?php

namespace MailVotech\LeadBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\LeadBundle\Entity\Company;
use MailVotech\ProjectBundle\Form\Type\ProjectType;
use MailVotech\UserBundle\Entity\User;
use MailVotech\UserBundle\Form\Type\UserListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<Company>
 */
final class CompanyType extends AbstractType
{
    use EntityFieldsBuildFormTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private RouterInterface $router,
        private TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $cleaningRules                 = $this->getFormFields($builder, $options, 'company');
        $cleaningRules['companyemail'] = 'email';

        $transformer = new IdToEntityModelTransformer($this->em, User::class);

        $builder->add(
            $builder->create(
                'owner',
                UserListType::class,
                [
                    'label'      => 'mailvotech.lead.company.field.owner',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class' => 'form-control',
                    ],
                    'required' => false,
                    'multiple' => false,
                ]
            )
                ->addModelTransformer($transformer)
        );

        $builder->add(
            'score',
            NumberType::class,
            [
                'label'      => 'mailvotech.company.score',
                'attr'       => ['class' => 'form-control'],
                'label_attr' => ['class' => 'control-label'],
                'scale'      => 0,
                'required'   => false,
            ]
        );

        $builder->add('projects', ProjectType::class);

        if (!empty($options['update_select'])) {
            $builder->add(
                'buttons',
                FormButtonsType::class,
                [
                    'apply_text' => false,
                ]
            );

            $builder->add(
                'updateSelect',
                HiddenType::class,
                [
                    'data'   => $options['update_select'],
                    'mapped' => false,
                ]
            );
        } else {
            $builder->add(
                'buttons',
                FormButtonsType::class
            );
        }

        if (null === $options['data']->getId()) {
            $builder->add(
                'buttons',
                FormButtonsType::class
            );
        } else {
            $builder->add(
                'buttons',
                FormButtonsType::class,
                [
                    'post_extra_buttons' => [
                        [
                            'name'  => 'merge',
                            'label' => 'mailvotech.lead.merge',
                            'attr'  => [
                                'class'       => 'btn btn-ghost btn-dnd',
                                'icon'        => 'ri-building-2-line',
                                'data-toggle' => 'ajaxmodal',
                                'data-target' => '#MailVotechSharedModal',
                                'data-header' => $this->translator->trans('mailvotech.lead.company.header.merge'),
                                'href'        => $this->router->generate(
                                    'mailvotech_company_action',
                                    [
                                        'objectId'     => $options['data']->getId(),
                                        'objectAction' => 'merge',
                                    ]
                                ),
                            ],
                        ],
                    ],
                ]
            );
        }

        $builder->addEventSubscriber(new CleanFormSubscriber($cleaningRules));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class'    => Company::class,
                'isShortForm'   => false,
                'update_select' => false,
            ]
        );

        $resolver->setRequired(['fields']);
    }
}
