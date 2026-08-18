<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechSocialBundle\Form\Type;

use MailVotech\CoreBundle\Form\Type\EntityLookupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<mixed>>
 */
final class TweetListType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'modal_route'         => 'mailvotech_tweet_action',
                'modal_header'        => 'mailvotech.integration.Twitter.new.tweet',
                'model'               => 'social.tweet',
                'model_lookup_method' => 'getLookupResults',
                'lookup_arguments'    => fn (Options $options): array => [
                    'type'   => 'tweet',
                    'filter' => '$data',
                    'limit'  => 0,
                    'start'  => 0,
                ],
                'ajax_lookup_action' => fn (Options $options): string => 'mailvotechSocial:getLookupChoiceList',
                'multiple'           => true,
                'required'           => false,
            ]
        );
    }

    public function getParent(): string
    {
        return EntityLookupType::class;
    }
}
