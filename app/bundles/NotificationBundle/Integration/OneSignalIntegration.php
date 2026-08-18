<?php

namespace MailVotech\NotificationBundle\Integration;

use MailVotech\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;

final class OneSignalIntegration extends AbstractIntegration
{
    protected bool $coreIntegration = true;

    public function getName(): string
    {
        return 'OneSignal';
    }

    public function getIcon(): string
    {
        return 'app/bundles/NotificationBundle/Assets/img/OneSignal.png';
    }

    public function getSupportedFeatures(): array
    {
        return [
            'mobile',
            'landing_page_enabled',
            'welcome_notification_enabled',
            'tracking_page_enabled',
        ];
    }

    public function getSupportedFeatureTooltips(): array
    {
        return [
            'landing_page_enabled'  => 'mailvotech.integration.form.features.landing_page_enabled.tooltip',
            'tracking_page_enabled' => 'mailvotech.integration.form.features.tracking_page_enabled.tooltip',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function getRequiredKeyFields(): array
    {
        return [
            'app_id'        => 'mailvotech.notification.config.form.notification.app_id',
            'safari_web_id' => 'mailvotech.notification.config.form.notification.safari_web_id',
            'rest_api_key'  => 'mailvotech.notification.config.form.notification.rest_api_key',
            'gcm_sender_id' => 'mailvotech.notification.config.form.notification.gcm_sender_id',
        ];
    }

    public function getAuthenticationType(): string
    {
        return 'none';
    }

    /**
     * @param Form|FormBuilder $builder
     * @param array            $data
     * @param string           $formArea
     */
    public function appendToForm(&$builder, $data, $formArea): void
    {
        if ('features' == $formArea) {
            $builder->add(
                'subdomain_name',
                TextType::class,
                [
                    'label'    => 'mailvotech.notification.form.subdomain_name.label',
                    'required' => false,
                    'attr'     => [
                        'class' => 'form-control',
                    ],
                ]
            );

            $builder->add(
                'platforms',
                ChoiceType::class,
                [
                    'choices' => [
                        'mailvotech.integration.form.platforms.ios'     => 'ios',
                        'mailvotech.integration.form.platforms.android' => 'android',
                    ],
                    'attr'              => [
                        'tooltip'      => 'mailvotech.integration.form.platforms.tooltip',
                        'data-show-on' => '{"integration_details_supportedFeatures_0":"checked"}',
                    ],
                    'expanded'    => true,
                    'multiple'    => true,
                    'label'       => 'mailvotech.integration.form.platforms',
                    'placeholder' => false,
                    'required'    => false,
                ]
            );
        }
    }
}
