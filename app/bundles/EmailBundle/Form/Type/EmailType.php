<?php

namespace MailVotech\EmailBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\AssetBundle\Entity\Asset;
use MailVotech\AssetBundle\Form\Type\AssetListType;
use MailVotech\CategoryBundle\Form\Type\CategoryListType;
use MailVotech\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\EventListener\FormExitSubscriber;
use MailVotech\CoreBundle\Form\Type\DynamicContentFilterType;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\PublishDownDateType;
use MailVotech\CoreBundle\Form\Type\PublishUpDateType;
use MailVotech\CoreBundle\Form\Type\SortableListType;
use MailVotech\CoreBundle\Form\Type\ThemeListType;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\ThemeHelperInterface;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Helper\EmailConfigInterface;
use MailVotech\EmailBundle\Helper\EmailDefaultsHelper;
use MailVotech\FormBundle\Entity\Form;
use MailVotech\FormBundle\Form\Type\FormListType;
use MailVotech\LeadBundle\Entity\LeadList;
use MailVotech\LeadBundle\Form\Type\LeadListType;
use MailVotech\LeadBundle\Helper\FormFieldHelper;
use MailVotech\PageBundle\Entity\Page;
use MailVotech\PageBundle\Form\Type\PreferenceCenterListType;
use MailVotech\ProjectBundle\Form\Type\ProjectType;
use MailVotech\StageBundle\Entity\StageRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<Email>
 */
final class EmailType extends AbstractType
{
    private readonly bool $isDraftEnabled;

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly EntityManagerInterface $em,
        private readonly CoreParametersHelper $coreParametersHelper,
        private readonly ThemeHelperInterface $themeHelper,
        private readonly CorePermissions $corePermissions,
        EmailConfigInterface $emailConfig,
        private readonly EmailDefaultsHelper $defaultsHelper,
        private readonly StageRepository $stageRepository,
    ) {
        $this->isDraftEnabled = $emailConfig->isDraftEnabled();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['content' => 'html', 'customHtml' => 'html', 'headers' => 'clean']));
        $builder->addEventSubscriber(new FormExitSubscriber('email.email', $options));

        $emailEntity =  $options['data'];
        \assert($emailEntity instanceof Email);

        // Apply only defaults that should be persisted on new emails, such as UTM tags.
        // Preference center fallback is resolved dynamically at unsubscribe time.
        $this->applyDefaultsForNewEmail($emailEntity);

        $builder->add(
            'name',
            TextType::class,
            [
                'label'      => 'mailvotech.email.form.internal.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
            ]
        );

        $builder->add(
            'subject',
            TextType::class,
            [
                'label'      => 'mailvotech.email.subject',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'onBlur'  => 'MailVotech.copySubjectToName(mQuery(this))',
                ],
            ]
        );

        $builder->add(
            'fromName',
            TextType::class,
            [
                'label'      => 'mailvotech.email.from_name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'preaddon' => 'ri-user-6-fill',
                    'tooltip'  => 'mailvotech.email.from_name.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'fromAddress',
            TextType::class,
            [
                'label'      => 'mailvotech.email.from_email',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'preaddon' => 'ri-mail-line',
                    'tooltip'  => 'mailvotech.email.from_email.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'replyToAddress',
            TextType::class,
            [
                'label'      => 'mailvotech.email.reply_to_email',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'preaddon' => 'ri-mail-line',
                    'tooltip'  => 'mailvotech.email.reply_to_email.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'bccAddress',
            TextType::class,
            [
                'label'      => 'mailvotech.email.bcc',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'preaddon' => 'ri-mail-line',
                    'tooltip'  => 'mailvotech.email.bcc.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'useOwnerAsMailer',
            YesNoButtonGroupType::class,
            [
                'label'      => 'mailvotech.email.use.owner.as.mailer',
                'label_attr' => ['class' => 'control-label'],
                'data'       => $this->getUseOwnerAsMailerOrDefaultValue($emailEntity),
                'required'   => false,
                'attr'       => [
                    'data-global-mailer-is-onwer' => (string) $this->getGlobalMailerIsOwner(),
                    'class'                       => 'form-control mailer-is-owner-local',
                    'tooltip'                     => 'mailvotech.email.use.owner.as.mailer.tooltip',
                    'data-warning'                => $this->translator->trans(
                        'mailvotech.email.config.mailer.is.owner.local.warning',
                        ['%value%' => $this->translator->trans($this->getGlobalMailerIsOwner() ? 'mailvotech.core.yes' : 'mailvotech.core.no')]
                    ),
                ],
            ]
        );

        $builder->add(
            'sendToDnc',
            YesNoButtonGroupType::class,
            [
                'label'    => 'mailvotech.email.send.dnc.label',
                'attr'     => [
                    'onchange'               => 'MailVotech.showSendToDncConfirmation(mQuery(this))',
                    'data-toggle'            => 'confirmation',
                    'data-message'           => $this->translator->trans('mailvotech.email.send.dnc.confirmation'),
                    'data-confirm-text'      => $this->translator->trans('mailvotech.email.send.dnc.confirmation.confirm.text'),
                    'data-confirm-callback'  => 'dismissConfirmation',
                    'data-cancel-text'       => $this->translator->trans('mailvotech.email.send.dnc.confirmation.cancel.text'),
                    'data-cancel-callback'   => 'setSendToDncToNo',
                    'data-confirm-btn-class' => 'btn btn-success',
                    'tooltip'                => 'mailvotech.email.send.dnc.tooltip',
                    'readonly'               => !$this->corePermissions->isGranted('email:emails:sendtodnc'),
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'utmTags',
            EmailUtmTagsType::class,
            [
                'label'      => 'mailvotech.email.utm_tags',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.email.utm_tags.tooltip',
                ],
                'required' => false,
            ]
        );

        $builder->add(
            'headers',
            SortableListType::class,
            [
                'required'        => false,
                'label'           => 'mailvotech.email.custom_headers',
                'attr'            => [
                    'tooltip' => 'mailvotech.email.custom_headers.tooltip',
                ],
                'option_required' => false,
                'with_labels'     => true,
                'key_value_pairs' => true, // do not store under a `list` key and use label as the key
            ]
        );

        $template = $emailEntity->getTemplate() ?? 'blank';
        if ($this->isDraftEnabled && $emailEntity->hasDraft() && !empty($emailEntity->getDraft()->getTemplate())) {
            $template = $emailEntity->getDraft()->getTemplate();
        }
        // If theme does not exist, set empty
        $template = $this->themeHelper->getCurrentTheme($template, 'email');

        $builder->add(
            'template',
            ThemeListType::class,
            [
                'feature' => 'email',
                'attr'    => [
                    'class'   => 'form-control not-chosen hidden',
                    'tooltip' => 'mailvotech.email.form.template.help',
                ],
                'data' => $template,
            ]
        );

        $canPublish = $this->corePermissions->hasPublishAccessForEntity($emailEntity, 'email:emails:publishown', 'email:emails:publishother');

        $isPublishOptions = [
            'data' => $emailEntity->isNew() ? $canPublish : $emailEntity->getIsPublished(),
        ];

        if (!$canPublish) {
            $isPublishOptions['disabled'] = true; // Duplicated here for Symfony validations
            $isPublishOptions['attr']     = ['disabled' => true]; // Duplicated here for the JS switch library
        }

        $builder->add('isPublished', YesNoButtonGroupType::class, $isPublishOptions);
        $builder->add('publishUp', PublishUpDateType::class, ['disabled' => !$canPublish]);
        $builder->add('publishDown', PublishDownDateType::class, ['disabled' => !$canPublish]);

        $builder->add(
            'plainText',
            TextareaType::class,
            [
                'label'      => 'mailvotech.email.form.plaintext',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'tooltip'              => 'mailvotech.email.form.plaintext.help',
                    'class'                => 'form-control',
                    'rows'                 => '15',
                    'data-token-callback'  => 'email:getBuilderTokens',
                    'data-token-activator' => '{',
                    'data-token-visual'    => 'false',
                ],
                'required' => false,
            ]
        );

        $html = $emailEntity->getCustomHtml();
        if ($this->isDraftEnabled && $emailEntity->hasDraft() && !empty($emailEntity->getDraft()->getHtml())) {
            $html = $emailEntity->getDraft()->getHtml();
        }
        $builder->add(
            'customHtml',
            TextareaType::class,
            [
                'label'      => 'mailvotech.email.form.body',
                'label_attr' => ['class' => 'control-label'],
                'required'   => false,
                'attr'       => [
                    'tooltip'              => 'mailvotech.email.form.body.help',
                    'class'                => 'form-control editor-builder-tokens builder-html editor-email',
                    'data-token-callback'  => 'email:getBuilderTokens',
                    'data-token-activator' => '{',
                    'rows'                 => '15',
                ],
                'data' => $html,
            ]
        );

        $transformer = new IdToEntityModelTransformer($this->em, Form::class, 'id');
        $builder->add(
            $builder->create(
                'unsubscribeForm',
                FormListType::class,
                [
                    'label'      => 'mailvotech.email.form.unsubscribeform',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'            => 'form-control',
                        'tooltip'          => 'mailvotech.email.form.unsubscribeform.tooltip',
                        'data-placeholder' => $this->translator->trans('mailvotech.core.form.chooseone'),
                    ],
                    'required'    => false,
                    'multiple'    => false,
                    'placeholder' => '',
                ]
            )
                ->addModelTransformer($transformer)
        );

        $transformer = new IdToEntityModelTransformer($this->em, Page::class, 'id');
        $builder->add(
            $builder->create(
                'preferenceCenter',
                PreferenceCenterListType::class,
                [
                    'label'      => 'mailvotech.email.form.preference_center',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'            => 'form-control',
                        'tooltip'          => 'mailvotech.email.form.preference_center.tooltip',
                        'data-placeholder' => $this->translator->trans('mailvotech.core.form.chooseone'),
                    ],
                    'required'    => false,
                    'multiple'    => false,
                    'placeholder' => '',
                ]
            )
                ->addModelTransformer($transformer)
        );

        $transformer = new IdToEntityModelTransformer($this->em, Email::class);
        $builder->add(
            $builder->create(
                'variantParent',
                HiddenType::class
            )->addModelTransformer($transformer)
        );

        $builder->add(
            $builder->create(
                'translationParent',
                HiddenType::class
            )->addModelTransformer($transformer)
        );

        $variantParent     = $emailEntity->getVariantParent();
        $translationParent = $emailEntity->getTranslationParent();
        $builder->add(
            'segmentTranslationParent',
            EmailListType::class,
            [
                'label'      => 'mailvotech.core.form.translation_parent',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.core.form.translation_parent.help',
                ],
                'required'       => false,
                'multiple'       => false,
                'email_type'     => 'list',
                'placeholder'    => 'mailvotech.core.form.translation_parent.empty',
                'top_level'      => 'translation',
                'variant_parent' => $variantParent ? $variantParent->getId() : null,
                'ignore_ids'     => [(int) $emailEntity->getId()],
                'mapped'         => false,
                'data'           => $translationParent ? $translationParent->getId() : null,
            ]
        );

        $builder->add(
            'templateTranslationParent',
            EmailListType::class,
            [
                'label'      => 'mailvotech.core.form.translation_parent',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.core.form.translation_parent.help',
                ],
                'required'       => false,
                'multiple'       => false,
                'placeholder'    => 'mailvotech.core.form.translation_parent.empty',
                'top_level'      => 'translation',
                'variant_parent' => $variantParent ? $variantParent->getId() : null,
                'email_type'     => 'template',
                'ignore_ids'     => [(int) $emailEntity->getId()],
                'mapped'         => false,
                'data'           => $translationParent ? $translationParent->getId() : null,
            ]
        );

        $variantSettingsModifier = function (FormEvent $event, bool $isParent, bool $isExisting = false): void {
            $event->getForm()->add(
                'variantSettings',
                VariantType::class,
                [
                    'label'       => 'mailvotech.core.ab_test.form.abtest_settings',
                    'required'    => false,
                    'is_parent'   => $isParent,
                    'is_existing' => $isExisting,
                    'data'        => $event->getData() instanceof Email ? $event->getData()->getVariantSettings() : [],
                ]
            );
        };

        // Building the form
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($variantSettingsModifier): void {
                /** @var Email $emailEntity */
                $emailEntity     = $event->getData();
                $variantChildren = $emailEntity->getVariantChildren();
                $isParent        = $variantChildren && count($variantChildren) > 0 || $event->getData()->isNew();
                $isExisting      = $event->getData()->getId() > 0;
                $variantSettingsModifier($event, $isParent, $isExisting);
            }
        );

        // After submit
        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($variantSettingsModifier): void {
                $data = $event->getData();

                /** @var Email $emailEntity */
                $emailEntity     = $event->getForm()->getData();
                $variantChildren = $emailEntity->getVariantChildren();
                $isParent        = $variantChildren && count($variantChildren) > 0;

                $variantSettingsModifier($event, $isParent);

                $emailType = $data['emailType'] ?? null;

                if ('list' === $emailType && isset($data['segmentTranslationParent'])) {
                    $data['translationParent'] = $data['segmentTranslationParent'];
                } elseif (isset($data['templateTranslationParent'])) {
                    $data['translationParent'] = $data['templateTranslationParent'];
                }

                $event->setData($data);
            }
        );

        $builder->add(
            'category',
            CategoryListType::class,
            [
                'bundle' => 'email',
            ]
        );

        $transformer = new IdToEntityModelTransformer($this->em, LeadList::class, 'id', true);
        $builder->add(
            $builder->create(
                'lists',
                LeadListType::class,
                [
                    'label'      => 'mailvotech.email.form.list',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'        => 'form-control',
                        'data-show-on' => '{"emailform_segmentTranslationParent":[""]}',
                    ],
                    'multiple' => true,
                    'expanded' => false,
                    'required' => true,
                ]
            )
                ->addModelTransformer($transformer)
        );

        $builder->add(
            $builder->create(
                'excludedLists',
                LeadListType::class,
                [
                    'label'      => 'mailvotech.email.form.excluded_list',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class' => 'form-control',
                    ],
                    'multiple'   => true,
                    'expanded'   => false,
                ]
            )
                ->addModelTransformer($transformer)
        );

        $builder->add(
            'language',
            LocaleType::class,
            [
                'label'      => 'mailvotech.core.language',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'required' => true,
            ]
        );

        $builder->add('projects', ProjectType::class);

        $transformer = new IdToEntityModelTransformer(
            $this->em,
            Asset::class,
            'id',
            true
        );
        $builder->add(
            $builder->create(
                'assetAttachments',
                AssetListType::class,
                [
                    'label'      => 'mailvotech.email.attachments',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'    => 'form-control',
                        'onchange' => 'MailVotech.getTotalAttachmentSize();',
                        'tooltip'  => 'mailvotech.email.attachments.help',
                    ],
                    'multiple' => true,
                    'expanded' => false,
                ]
            )
                ->addModelTransformer($transformer)
        );

        $builder->add('sessionId', HiddenType::class);
        $builder->add('emailType', HiddenType::class);

        $extraButtons                      = [];
        $extraButtons['pre_extra_buttons'] = [
            [
                'name'  => 'builder',
                'label' => 'mailvotech.core.builder',
                'attr'  => [
                    'class'   => 'btn btn-tertiary btn-dnd btn-nospin text-interactive btn-builder',
                    'icon'    => 'ri-layout-line',
                    'onclick' => "MailVotech.launchBuilder('{$this->getBlockPrefix()}', 'email');",
                ],
            ],
        ];

        $draftActionButtons = $this->getDraftActionButtons($emailEntity);
        if ([] !== $draftActionButtons) {
            $extraButtons['post_extra_buttons'] = $draftActionButtons;
        }
        $builder->add(
            'buttons',
            FormButtonsType::class,
            $extraButtons
        );

        $builder->add(
            $builder->create(
                'preheaderText',
                TextType::class,
                [
                    'label'      => 'mailvotech.email.preheader_text',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'    => 'form-control',
                        'tooltip'  => 'mailvotech.email.preheader_text.tooltip',
                    ],
                    'required'    => false,
                ]
            )
        );

        $builder->add(
            $builder->create(
                'preheaderText',
                TextType::class,
                [
                    'label'      => 'mailvotech.email.preheader_text',
                    'label_attr' => ['class' => 'control-label'],
                    'attr'       => [
                        'class'    => 'form-control',
                        'tooltip'  => 'mailvotech.email.preheader_text.tooltip',
                    ],
                    'required'    => false,
                ]
            )
        );

        if (!empty($options['update_select'])) {
            $builder->add(
                'updateSelect',
                HiddenType::class,
                [
                    'data'   => $options['update_select'],
                    'mapped' => false,
                ]
            );
        }

        $this->addDynamicContentField($builder);

        $builder->add('version', HiddenType::class, [
            'mapped' => false,
        ]);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    /**
     * @return mixed[]
     */
    private function getDraftActionButtons(Email $email): array
    {
        $draftActionButtons = [];
        if (false === $this->isDraftEnabled || empty($email->getId())) {
            return $draftActionButtons;
        }

        if ($email->hasDraft()) {
            $draftActionButtons[] = [
                'name'  => 'apply_draft',
                'label' => 'mailvotech.core.applydraft',
                'type'  => SubmitType::class,
                'attr'  => [
                    'class'   => 'btn btn-primary btn-apply-draft',
                    'icon'    => 'fa fa-files-o text-success',
                ],
            ];
            $draftActionButtons[] = [
                'name'  => 'discard_draft',
                'label' => 'mailvotech.core.discarddraft',
                'type'  => SubmitType::class,
                'attr'  => [
                    'class'   => 'btn btn-primary btn-discard-draft',
                    'icon'    => 'fa fa-trash text-danger',
                ],
            ];
        } else {
            $draftActionButtons[] = [
                'name'  => 'save_draft',
                'label' => 'mailvotech.core.saveasdraft',
                'type'  => SubmitType::class,
                'attr'  => [
                    'class'   => 'btn btn-primary btn-save-draft',
                    'icon'    => 'fa fa-file text-success',
                ],
            ];
        }

        return $draftActionButtons;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => Email::class,
            ]
        );

        $resolver->setDefined(['update_select']);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $stages       = $this->stageRepository->getSimpleList();
        $stageChoices = [];

        foreach ($stages as $stage) {
            $stageChoices[$stage['value']] = $stage['label'];
        }

        $view->vars['countries'] = FormFieldHelper::getCountryChoices();
        $view->vars['regions']   = FormFieldHelper::getRegionChoices();
        $view->vars['timezones'] = FormFieldHelper::getTimezonesChoices();
        $view->vars['locales']   = FormFieldHelper::getLocaleChoices();
        $view->vars['stages']    = $stageChoices;
    }

    public function getBlockPrefix(): string
    {
        return 'emailform';
    }

    /**
     * The owner as mailer value will be taken from the email entity unless the email is new.
     * If so, it will choose the value from the global configuration as default.
     */
    private function getUseOwnerAsMailerOrDefaultValue(Email $email): bool
    {
        return $email->getId() ? ((bool) $email->getUseOwnerAsMailer()) : $this->getGlobalMailerIsOwner();
    }

    private function getGlobalMailerIsOwner(): bool
    {
        return (bool) $this->coreParametersHelper->get('mailer_is_owner');
    }

    private function applyDefaultsForNewEmail(Email $emailEntity): void
    {
        if (!$emailEntity->isNew() || $emailEntity->getIsClone()) {
            return;
        }

        $this->defaultsHelper->applyDefaults($emailEntity);
    }

    private function addDynamicContentField(FormBuilderInterface $builder): void
    {
        $builder->add(
            'dynamicContent',
            CollectionType::class,
            [
                'entry_type'         => DynamicContentFilterType::class,
                'allow_add'          => true,
                'allow_delete'       => true,
                'label'              => false,
                'entry_options'      => [
                    'label' => false,
                ],
            ]
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event): void {
                $data = $event->getData();
                /** @var Email $entity */
                $entity = $event->getForm()->getData();

                if (empty($data['dynamicContent'])) {
                    $data['dynamicContent'] = $entity->getDefaultDynamicContent();
                    unset($data['dynamicContent'][0]['filters']['filter']);
                }

                foreach ($data['dynamicContent'] as $key => $dc) {
                    if (empty($dc['filters'])) {
                        $data['dynamicContent'][$key]['filters'] = $entity->getDefaultDynamicContent()[0]['filters'];
                    }
                }

                $event->setData($data);
            }
        );
    }
}
