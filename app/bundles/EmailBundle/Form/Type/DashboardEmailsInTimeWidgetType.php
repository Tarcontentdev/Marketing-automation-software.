<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Form\Type;

use MailVotech\CampaignBundle\Form\Type\CampaignListType;
use MailVotech\LeadBundle\Form\Type\CompanyListType;
use MailVotech\LeadBundle\Form\Type\LeadListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<mixed>
 */
final class DashboardEmailsInTimeWidgetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'flag',
            ChoiceType::class,
            [
                'label'             => 'mailvotech.email.flag.filter',
                'choices'           => [
                    'mailvotech.email.flag.sent'                       => '',
                    'mailvotech.email.flag.opened'                     => 'opened',
                    'mailvotech.email.flag.failed'                     => 'failed',
                    'mailvotech.email.flag.sent.and.opened'            => 'sent_and_opened',
                    'mailvotech.email.flag.sent.and.opened.and.failed' => 'sent_and_opened_and_failed',
                ],
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'empty_data' => '',
                'required'   => false,
            ]
        );

        $builder->add(
            'companyId',
            CompanyListType::class,
            [
                'label'       => 'mailvotech.email.companyId.filter',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'placeholder' => '',
                'required'    => false,
                'multiple'    => false,
                'modal_route' => null,
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
                'label'       => 'mailvotech.email.segmentId.filter',
                'label_attr'  => ['class' => 'control-label'],
                'attr'        => ['class' => 'form-control'],
                'placeholder' => '',
                'required'    => false,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'email_dashboard_emails_in_time_widget';
    }
}
