<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<mixed>
 */
final class CampaignEventLeadSegmentsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'segments',
            LeadListType::class,
            [
                'global_only' => true,
                'label'       => 'mailvotech.lead.lead.lists',
                'label_attr'  => ['class' => 'control-label'],
                'multiple'    => true,
                'required'    => false,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'campaignevent_lead_segments';
    }
}
