<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<mixed>
 */
final class DashboardLeadsInTimeWidgetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'flag',
            ChoiceType::class,
            [
                'label'             => 'mailvotech.lead.list.filter',
                'choices'           => [
                    'mailvotech.lead.show.all'                               => '',
                    'mailvotech.lead.show.identified'                        => 'identified',
                    'mailvotech.lead.show.anonymous'                         => 'anonymous',
                    'mailvotech.lead.show.identified.vs.anonymous'           => 'identifiedVsAnonymous',
                    'mailvotech.lead.show.top'                               => 'top',
                    'mailvotech.lead.show.top.leads.identified.vs.anonymous' => 'topIdentifiedVsAnonymous',
                ],
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'empty_data' => '',
                'required'   => false,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'lead_dashboard_leads_in_time_widget';
    }
}
