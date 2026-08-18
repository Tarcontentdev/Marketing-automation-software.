<?php

declare(strict_types=1);

namespace MailVotech\AssetBundle\Form\Type;

use MailVotech\CategoryBundle\Form\Type\CategoryListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<mixed>
 */
final class FormSubmitActionDownloadFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'asset',
            AssetListType::class,
            [
                'expanded'    => false,
                'multiple'    => false,
                'label'       => 'mailvotech.asset.form.submit.assets',
                'label_attr'  => ['class' => 'control-label'],
                'placeholder' => 'mailvotech.asset.form.submit.latest.category',
                'required'    => false,
                'attr'        => [
                    'class'   => 'form-control',
                    'tooltip' => 'mailvotech.asset.form.submit.assets_descr',
                ],
            ]
        );

        $builder->add(
            'category',
            CategoryListType::class,
            [
                'label'         => 'mailvotech.asset.form.submit.latest.category',
                'label_attr'    => ['class' => 'control-label'],
                'placeholder'   => false,
                'required'      => false,
                'bundle'        => 'asset',
                'return_entity' => false,
                'attr'          => [
                    'class'        => 'form-control',
                    'tooltip'      => 'mailvotech.asset.form.submit.latest.category_descr',
                    'data-show-on' => '{"formaction_properties_asset":""}',
                ],
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'asset_submitaction_downloadfile';
    }
}
