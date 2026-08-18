<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Form\Type;

use MailVotech\UserBundle\Form\Type\UserListType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<mixed>
 */
final class CampaignEventLeadOwnerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'owner',
            UserListType::class,
            [
                'label'      => 'mailvotech.lead.lead.field.owner',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class' => 'form-control',
                ],
                'required' => false,
                'multiple' => true,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'campaignevent_lead_owner';
    }
}
