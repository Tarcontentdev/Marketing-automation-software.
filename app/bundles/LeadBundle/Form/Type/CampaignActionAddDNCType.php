<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<mixed>
 */
final class CampaignActionAddDNCType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'channels',
            PreferenceChannelsType::class,
            [
                'label'       => 'mailvotech.lead.contact.channels',
                'multiple'    => true,
                'required'    => true,
                'constraints' => [
                    new NotBlank(),
                ],
            ]
        );

        $builder->add(
            'reason',
            TextareaType::class,
            [
                'label'      => 'mailvotech.lead.batch.dnc_reason',
                'required'   => false,
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
            ]
        );
    }
}
