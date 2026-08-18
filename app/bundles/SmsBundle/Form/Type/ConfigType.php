<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\Form\Type;

use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\SmsBundle\Sms\TransportChain;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<array<mixed>>
 */
final class ConfigType extends AbstractType
{
    public const SMS_DISABLE_TRACKABLE_URLS = 'sms_disable_trackable_urls';

    public function __construct(
        private readonly TransportChain $transportChain,
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * @param FormBuilderInterface<array<mixed>|null> $builder
     * @param array<string, mixed>                    $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices    = [];
        $transports = $this->transportChain->getEnabledTransports();
        foreach ($transports as $transportServiceId=>$transport) {
            $choices[$this->translator->trans($transportServiceId)] = $transportServiceId;
        }

        $builder->add('sms_transport', ChoiceType::class, [
            'label'      => 'mailvotech.sms.config.select_default_transport',
            'label_attr' => ['class' => 'control-label'],
            'attr'       => [
                'class'   => 'form-control',
                'tooltip' => 'mailvotech.sms.config.select_default_transport',
            ],
            'choices'           => $choices,
        ]);

        $builder->add(
            self::SMS_DISABLE_TRACKABLE_URLS,
            YesNoButtonGroupType::class,
            [
                'label' => 'mailvotech.sms.config.form.sms.disable_trackable_urls',
                'attr'  => [
                    'tooltip' => 'mailvotech.sms.config.form.sms.disable_trackable_urls.tooltip',
                ],
                'data'=> !empty($options['data'][self::SMS_DISABLE_TRACKABLE_URLS]),
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'smsconfig';
    }
}
