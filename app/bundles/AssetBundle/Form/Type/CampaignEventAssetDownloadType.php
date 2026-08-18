<?php

declare(strict_types=1);

namespace MailVotech\AssetBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<mixed>
 */
final class CampaignEventAssetDownloadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'assets',
            AssetListType::class,
            [
                'label'      => 'mailvotech.asset.campaign.event.assets',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.asset.campaign.event.assets.descr',
                ],
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'campaignevent_assetdownload';
    }
}
