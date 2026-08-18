<?php

namespace MailVotechPlugin\MailVotechSocialBundle\Form\Type;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\AssetBundle\Entity\Asset;
use MailVotech\AssetBundle\Form\Type\AssetListType;
use MailVotech\CategoryBundle\Form\Type\CategoryListType;
use MailVotech\CoreBundle\Form\DataTransformer\IdToEntityModelTransformer;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\PageBundle\Entity\Page;
use MailVotech\PageBundle\Form\Type\PageListType;
use MailVotechPlugin\MailVotechSocialBundle\Entity\Tweet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<Tweet>
 */
final class TweetType extends AbstractType
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'label'      => 'mailvotech.social.monitoring.twitter.tweet.name',
                'required'   => true,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'tooltip' => 'mailvotech.social.monitoring.twitter.tweet.name.tooltip',
                    'class'   => 'form-control',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'mailvotech.core.name.required'
                    ),
                ],
            ]
        );

        $builder->add(
            'description',
            TextareaType::class,
            [
                'label'      => 'mailvotech.social.monitoring.twitter.tweet.description',
                'required'   => false,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'tooltip' => 'mailvotech.social.monitoring.twitter.tweet.description.tooltip',
                    'class'   => 'form-control',
                ],
            ]
        );

        $builder->add(
            'text',
            TextareaType::class,
            [
                'label'      => 'mailvotech.social.monitoring.twitter.tweet.text',
                'required'   => true,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'tooltip' => 'mailvotech.social.monitoring.twitter.tweet.text.tooltip',
                    'class'   => 'form-control tweet-message',
                ],
                'constraints' => [
                    new NotBlank(
                        message: 'mailvotech.core.value.required'
                    ),
                ],
            ]
        );

        $transformer = new IdToEntityModelTransformer($this->em, Asset::class, 'id');
        $builder->add(
            $builder->create(
                'asset',
                AssetListType::class,
                [
                    'label'       => 'mailvotech.social.monitoring.twitter.assets',
                    'placeholder' => 'mailvotech.social.monitoring.list.choose',
                    'label_attr'  => ['class' => 'control-label'],
                    'multiple'    => false,
                    'attr'        => [
                        'class'   => 'form-control tweet-insert-asset',
                        'tooltip' => 'mailvotech.social.monitoring.twitter.assets.descr',
                    ],
                ]
            )->addModelTransformer($transformer)
        );

        $transformer = new IdToEntityModelTransformer($this->em, Page::class, 'id');
        $builder->add(
            $builder->create(
                'page',
                PageListType::class,
                [
                    'label'       => 'mailvotech.social.monitoring.twitter.pages',
                    'placeholder' => 'mailvotech.social.monitoring.list.choose',
                    'label_attr'  => ['class' => 'control-label'],
                    'multiple'    => false,
                    'attr'        => [
                        'class'   => 'form-control tweet-insert-page',
                        'tooltip' => 'mailvotech.social.monitoring.twitter.pages.descr',
                    ],
                ]
            )->addModelTransformer($transformer)
        );

        $builder->add(
            'handle',
            ButtonType::class,
            [
                'label' => 'mailvotech.social.twitter.handle',
                'attr'  => [
                    'class' => 'form-control btn-primary tweet-insert-handle',
                ],
            ]
        );

        // add category
        $builder->add('category', CategoryListType::class, [
            'bundle' => 'plugin:mailvotechSocial',
        ]);

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

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['update_select']);
    }

    public function getBlockPrefix(): string
    {
        return 'twitter_tweet';
    }
}
