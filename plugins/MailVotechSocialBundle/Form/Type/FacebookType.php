<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechSocialBundle\Form\Type;

use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<array<mixed>>
 */
final class FacebookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('layout', ChoiceType::class, [
            'choices' => [
                'mailvotech.integration.Facebook.share.layout.standard'    => 'standard',
                'mailvotech.integration.Facebook.share.layout.buttoncount' => 'button_count',
                'mailvotech.integration.Facebook.share.layout.button'      => 'button',
                'mailvotech.integration.Facebook.share.layout.boxcount'    => 'box_count',
                'mailvotech.integration.Facebook.share.layout.icon'        => 'icon',
            ],
            'label'             => 'mailvotech.integration.Facebook.share.layout',
            'required'          => false,
            'placeholder'       => false,
            'label_attr'        => ['class' => 'control-label'],
            'attr'              => ['class' => 'form-control'],
        ]);

        $builder->add('action', ChoiceType::class, [
            'choices' => [
                'mailvotech.integration.Facebook.share.action.like'      => 'like',
                'mailvotech.integration.Facebook.share.action.recommend' => 'recommend',
                'mailvotech.integration.Facebook.share.action.share'     => 'share',
            ],
            'label'             => 'mailvotech.integration.Facebook.share.action',
            'required'          => false,
            'placeholder'       => false,
            'label_attr'        => ['class' => 'control-label'],
            'attr'              => ['class' => 'form-control'],
        ]);

        $builder->add('showFaces', YesNoButtonGroupType::class, [
            'label' => 'mailvotech.integration.Facebook.share.showfaces',
            'data'  => (!isset($options['data']['showFaces'])) ? 1 : $options['data']['showFaces'],
        ]);

        $builder->add('showShare', YesNoButtonGroupType::class, [
            'label' => 'mailvotech.integration.Facebook.share.showshare',
            'data'  => (!isset($options['data']['showShare'])) ? 1 : $options['data']['showShare'],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'socialmedia_facebook';
    }
}
