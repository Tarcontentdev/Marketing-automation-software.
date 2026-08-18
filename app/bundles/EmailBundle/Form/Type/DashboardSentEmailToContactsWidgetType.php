<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Form\Type;

use MailVotech\CampaignBundle\Form\Type\CampaignListType;
use MailVotech\LeadBundle\Form\Type\CompanyListType;
use MailVotech\LeadBundle\Form\Type\LeadListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<mixed>
 */
final class DashboardSentEmailToContactsWidgetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'companyId',
            CompanyListType::class,
            [
                'label'       => 'mailvotech.email.companyId.filter',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'empty_data'  => '',
                'required'    => false,
                'multiple'    => false,
                'modal_route' => null, // disable "Add new" option in ajax lookup
            ]
        );

        $builder->add(
            'campaignId',
            CampaignListType::class,
            [
                'label'       => 'mailvotech.email.campaignId.filter',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'empty_data'  => '',
                'placeholder' => '',
                'required'    => false,
                'multiple'    => false,
            ]
        );

        $builder->add(
            'segmentId',
            LeadListType::class,
            [
                'label'      => 'mailvotech.email.segmentId.filter',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'empty_data' => '',
                'required'   => false,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'email_dashboard_sent_email_to_contacts_widget';
    }
}
