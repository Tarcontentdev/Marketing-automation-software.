<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechTagManagerBundle\Form\Type;

use Doctrine\Common\Collections\Order;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\LeadBundle\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class TagMergeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'tag_to_merge',
            EntityType::class,
            [
                'class'           => Tag::class,
                'choice_label'    => 'tag',
                'multiple'        => false,
                'label'           => 'mailvotech.tagmanager.to.merge.into',
                'required'        => true,
                'query_builder'   => function ($er) use ($options) {
                    $qb = $er->createQueryBuilder('t')->orderBy('t.tag', Order::Ascending->value);

                    if (!empty($options['exclude_ids'])) {
                        $qb->andWhere('t.id NOT IN (:exclude_ids)')
                           ->setParameter('exclude_ids', $options['exclude_ids']);
                    }

                    return $qb;
                },
                'constraints'     => [
                    new NotBlank(message: 'mailvotech.tagmanager.tag.choose.notblank'),
                ],
            ]
        );

        $builder->add(
            'buttons',
            FormButtonsType::class,
            [
                'apply_text' => false,
                'save_text'  => 'mailvotech.lead.merge',
                'save_icon'  => 'ri-hashtag',
            ]
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['action', 'exclude_ids']);
    }
}
