<?php

namespace MailVotech\AssetBundle\Form\Type;

use MailVotech\AssetBundle\Entity\Asset;
use MailVotech\AssetBundle\Model\AssetModel;
use MailVotech\CategoryBundle\Form\Type\CategoryListType;
use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\EventListener\FormExitSubscriber;
use MailVotech\CoreBundle\Form\Type\ButtonGroupType;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\PublishDownDateType;
use MailVotech\CoreBundle\Form\Type\PublishUpDateType;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\ProjectBundle\Form\Type\ProjectType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<Asset>
 */
final class AssetType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly AssetModel $assetModel,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['description' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('asset.asset', $options));

        $builder->add('storageLocation', ButtonGroupType::class, [
            'label'   => 'mailvotech.asset.asset.form.storageLocation',
            'choices' => [
                'mailvotech.asset.asset.form.storageLocation.local'  => 'local',
                'mailvotech.asset.asset.form.storageLocation.remote' => 'remote',
            ],
            'attr'              => [
                'onchange' => 'MailVotech.changeAssetStorageLocation();',
            ],
        ]);

        $maxUploadSize = $this->assetModel->getMaxUploadSize('', true);
        $builder->add(
            'tempName',
            HiddenType::class,
            [
                'label'       => $this->translator->trans('mailvotech.asset.asset.form.file.upload', ['%max%' => $maxUploadSize]),
                'label_attr'  => ['class' => 'control-label'],
                'required'    => false,
            ]
        );

        $builder->add(
            'originalFileName',
            HiddenType::class,
            [
                'required'    => false,
            ],
        );
        $builder->add(
            'disallow',
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.asset.asset.form.disallow.crawlers',
                'attr'  => [
                    'tooltip'      => 'mailvotech.asset.asset.form.disallow.crawlers.descr',
                    'data-show-on' => '{"asset_storageLocation_0":"checked"}',
                ],
                'data'=> !empty($options['data']->getDisallow()),
            ]
        );

        $builder->add(
            'remotePath',
            TextType::class,
            [
                'label'       => 'mailvotech.asset.asset.form.remotePath',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'required'    => false,
            ]
        );

        $builder->add(
            'title',
            TextType::class,
            [
                'label'      => 'mailvotech.core.title',
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
                'bundle' => 'asset',
            ]
        );

        $builder->add('projects', ProjectType::class);

        $builder->add('language', LocaleType::class, [
            'label'      => 'mailvotech.core.language',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'   => 'form-control',
                'tooltip' => 'mailvotech.asset.asset.form.language.help',
            ],
            'required'    => true,
            'constraints' => [
                new NotBlank(
                    message: 'mailvotech.core.value.required'
                ),
            ],
        ]);

        $builder->add('isPublished', YesNoButtonGroupType::class, [
            'label' => 'mailvotech.core.form.available',
        ]);
        $builder->add('publishUp', PublishUpDateType::class);
        $builder->add('publishDown', PublishDownDateType::class);

        $builder->add(
            'tempId',
            HiddenType::class,
            [
                'required' => false,
            ]
        );

        $builder->add('buttons', FormButtonsType::class, []);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Asset::class]);
    }
}
