<?php

namespace MailVotech\LeadBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\EventListener\FormExitSubscriber;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Model\CompanyModel;
use MailVotech\StageBundle\Entity\Stage;
use MailVotech\StageBundle\Form\Type\StageListType;
use MailVotech\UserBundle\Entity\User;
use MailVotech\UserBundle\Form\Type\UserListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<Lead>
 */
final class LeadType extends AbstractType
{
    use EntityFieldsBuildFormTrait;

    public function __construct(
        private TranslatorInterface $translator,
        private CompanyModel $companyModel,
        private EntityManagerInterface $entityManager,
        private CoreParametersHelper $coreParametersHelper,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new FormExitSubscriber('lead.lead', $options));

        if (!$options['isShortForm']) {
            $imageChoices = [
                'Gravatar'                             => 'gravatar',
                'mailvotech.lead.lead.field.custom_avatar' => 'custom',
            ];

            $cache = $options['data']->getSocialCache() ?? [];
            if (count($cache)) {
                foreach ($cache as $key => $data) {
                    $imageChoices[$key] = $key;
                }
            }

            $builder->add(
                'preferred_profile_image',
                ChoiceType::class,
                [
                    'choices'           => $imageChoices,
                    'label'             => 'mailvotech.lead.lead.field.preferred_profile',
                    'label_attr'        => ['class' => 'control-label'],
                    'attr'              => ['class' => 'form-control'],
                    'required'          => true,
                    'multiple'          => false,
                ]
            );

            $builder->add(
                'custom_avatar',
                FileType::class,
                [
                    'label'      => false,
                    'label_attr' => ['class' => 'control-label'],
                    'required'   => false,
                    'attr'       => [
                        'class' => 'form-control',
                    ],
                    'mapped'      => false,
                    'constraints' => [
                        new File(mimeTypes: [
                            'image/gif',
                            'image/jpeg',
                            'image/png',
                        ], mimeTypesMessage: 'mailvotech.lead.avatar.types_invalid'),
                    ],
                ]
            );
        }

        $cleaningRules          = $this->getFormFields($builder, $options);
        $cleaningRules['email'] = 'email';

        $builder->add(
            'tags',
            TagType::class,
            [
                'by_reference' => false,
                'attr'         => [
                    'id'                   => 'lead_tags',
                    'data-placeholder'     => $this->translator->trans('mailvotech.lead.tags.select_or_create'),
                    'data-no-results-text' => $this->translator->trans('mailvotech.lead.tags.enter_to_create'),
                    'data-allow-add'       => 'true',
                    'onchange'             => 'MailVotech.createLeadTag(this)',
                    'autocomplete'         => 'off',
                    'multiple'             => 'multiple',
                    'aria-label'           => $this->translator->trans('mailvotech.lead.tags.aria.label'),
                    'aria-describedby'     => 'lead_tags_help',
                    'aria-expanded'        => 'false',
                    'role'                 => 'combobox',
                    'aria-multiselectable' => 'true',
                ],
            ]
        );

        $allowMultipleCompanies = $this->coreParametersHelper->get('contact_allow_multiple_companies');
        $companyIds             = $this->companyModel->getCompanyLeadRepository()->getCompanyIdsByLeadId((string) $options['data']->getId());

        $builder->add(
            'companies',
            CompanyListType::class,
            [
                'label'      => 'mailvotech.company.selectcompany',
                'label_attr' => ['class' => 'control-label'],
                'multiple'   => $allowMultipleCompanies,
                'required'   => false,
                'mapped'     => false,
                'data'       => !$allowMultipleCompanies ? ($companyIds[0] ?? null) : array_combine($companyIds, $companyIds),
            ]
        );

        $transformer = new IdToEntityModelTransformer($this->entityManager, User::class);

        $builder->add(
            $builder->create(
                'owner',
                UserListType::class,
                [
                    'label'      => 'mailvotech.lead.lead.field.owner',
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

        $transformer = new IdToEntityModelTransformer($this->entityManager, Stage::class);

        $builder->add(
            $builder->create(
                'stage',
                StageListType::class,
                [
                    'label'      => 'mailvotech.lead.lead.field.stage',
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

        if (!$options['isShortForm']) {
            $builder->add('buttons', FormButtonsType::class);
        } else {
            $builder->add(
                'buttons',
                FormButtonsType::class,
                [
                    'apply_text' => false,
                    'save_text'  => 'mailvotech.core.form.save',
                ]
            );
        }

        $builder->addEventSubscriber(new CleanFormSubscriber($cleaningRules));

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class'  => Lead::class,
                'isShortForm' => false,
            ]
        );

        $resolver->setRequired(['fields', 'isShortForm']);
    }
}
