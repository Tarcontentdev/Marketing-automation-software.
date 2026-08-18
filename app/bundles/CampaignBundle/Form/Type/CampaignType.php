<?php

namespace MailVotech\CampaignBundle\Form\Type;

use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\CategoryBundle\Form\Type\CategoryListType;
use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\EventListener\FormExitSubscriber;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\PublishDownDateType;
use MailVotech\CoreBundle\Form\Type\PublishUpDateType;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\ProjectBundle\Form\Type\ProjectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<Campaign>
 */
final class CampaignType extends AbstractType
{
    public function __construct(
        private readonly CorePermissions $security,
        private readonly TranslatorInterface $translator,
        private readonly CoreParametersHelper $coreParametersHelper,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('campaign', $options));

        $builder->add('name', TextType::class, [
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

        $builder->add('allowRestart',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.campaign.allow_restart',
                'attr'  => [
                    'tooltip' => 'mailvotech.campaign.allow_restart.tooltip',
                ],
            ]
        );

        // add category
        $builder->add('category', CategoryListType::class, [
            'bundle' => 'campaign',
        ]);

        $attr = [];
        /** @var ?Campaign $campaign */
        $campaign = $options['data'] ?? null;
        if ($campaign && $campaign->getId()) {
            $readonly          = !$this->security->isGranted('campaign:campaigns:publish');
            $data              = $campaign->isPublished(false);
            $republishBehavior = $campaign->getRepublishBehavior() ?? $this->coreParametersHelper->get('campaign_republish_behavior');
            $republishBehavior = $this->translator->trans('mailvotech.campaignconfig.campaign_republish_behavior.'.$republishBehavior);
            $attr              = [
                'onchange'               => 'MailVotech.showCampaignConfirmation(mQuery(this));',
                'data-toggle'            => 'confirmation',
                'data-message-publish'   => $this->translator->trans('mailvotech.campaign.form.confirmation.message.publish', ['%republishBehavior%' => $republishBehavior]),
                'data-message-unpublish' => $this->translator->trans('mailvotech.campaign.form.confirmation.message'),
                'data-confirm-text'      => $this->translator->trans('mailvotech.campaign.form.confirmation.confirm_text'),
                'data-confirm-callback'  => 'dismissConfirmation',
                'data-cancel-text'       => $this->translator->trans('mailvotech.campaign.form.confirmation.cancel_text'),
                'data-cancel-callback'   => 'setPublishedButtonToYes',
                'class'                  => 'btn btn-ghost',
            ];
        } elseif (!$this->security->isGranted('campaign:campaigns:publish')) {
            $readonly = true;
            $data     = false;
        } else {
            $readonly = false;
            $data     = false;
        }

        $attr['readonly'] = $readonly;

        $builder->add('isPublished', YesNoButtonGroupType::class, [
            'data' => $data,
            'attr' => $attr,
        ]);

        $builder->add('publishUp', PublishUpDateType::class);
        $builder->add('publishDown', PublishDownDateType::class);

        $builder->add(
            'republishBehavior',
            RepublishBehaviorType::class,
            [
                'include_global_option' => true,
            ]
        );

        $builder->add('sessionId', HiddenType::class, [
            'mapped' => false,
        ]);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }

        $builder->add('projects', ProjectType::class);

        $builder->add('buttons', FormButtonsType::class, [
            'pre_extra_buttons' => [
                [
                    'name'  => 'builder',
                    'label' => 'mailvotech.campaign.campaign.launch.builder',
                    'attr'  => [
                        'class'   => 'btn btn-tertiary btn-dnd',
                        'icon'    => 'ri-organization-chart',
                        'onclick' => 'MailVotech.launchCampaignEditor();',
                    ],
                ],
            ],
        ]);

        $builder->add('version', HiddenType::class, [
            'mapped' => false,
        ]);

        $builder->add('campaignElements', HiddenType::class, [
            'mapped' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Campaign::class,
        ]);
    }
}
