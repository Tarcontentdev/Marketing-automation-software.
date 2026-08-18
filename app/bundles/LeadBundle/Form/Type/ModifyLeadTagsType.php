<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<mixed>
 */
final class ModifyLeadTagsType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'add_tags',
            TagType::class,
            [
                'label' => 'mailvotech.lead.tags.add',
                'attr'  => [
                    'data-placeholder'     => $this->translator->trans('mailvotech.lead.tags.select_or_create'),
                    'data-no-results-text' => $this->translator->trans('mailvotech.lead.tags.enter_to_create'),
                    'data-allow-add'       => 'true',
                    'onchange'             => 'MailVotech.createLeadTag(this)',
                ],
                'data'            => $options['data']['add_tags'] ?? null,
                'add_transformer' => true,
            ]
        );

        $builder->add(
            'remove_tags',
            TagType::class,
            [
                'label' => 'mailvotech.lead.tags.remove',
                'attr'  => [
                    'data-placeholder'     => $this->translator->trans('mailvotech.lead.tags.select_or_create'),
                    'data-no-results-text' => $this->translator->trans('mailvotech.lead.tags.enter_to_create'),
                    'data-allow-add'       => 'true',
                    'onchange'             => 'MailVotech.createLeadTag(this)',
                ],
                'data'            => $options['data']['remove_tags'] ?? null,
                'add_transformer' => true,
            ]
        );
    }
}
