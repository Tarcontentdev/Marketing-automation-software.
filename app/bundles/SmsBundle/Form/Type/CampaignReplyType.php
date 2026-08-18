<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<array<mixed>>
 */
final class CampaignReplyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'pattern',
            TextType::class,
            [
                'label'      => 'mailvotech.sms.reply_pattern',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => [
                    'class'    => 'form-control',
                    'tooltip'  => 'mailvotech.sms.reply_pattern.tooltip',
                ],
                'required'    => false,
            ]
        );
    }
}
