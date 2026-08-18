<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<mixed>
 */
final class CampaignEventLeadTagsType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'tags',
            TagType::class,
            [
                'add_transformer' => true,
                'by_reference'    => false,
                'attr'            => [
                    'data-placeholder'     => $this->translator->trans('mailvotech.lead.tags.select_or_create'),
                    'data-no-results-text' => $this->translator->trans('mailvotech.lead.tags.enter_to_create'),
                    'data-allow-add'       => 'true',
                    'onchange'             => 'MailVotech.createLeadTag(this)',
                ],
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'campaignevent_lead_tags';
    }
}
