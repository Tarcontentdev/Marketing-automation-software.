<?php

namespace MailVotechPlugin\MailVotechFullContactBundle\Integration;

use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\PluginBundle\Integration\AbstractIntegration;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class FullContactIntegration extends AbstractIntegration
{
    public function getName(): string
    {
        return 'FullContact';
    }

    /**
     * Return's authentication method such as oauth2, oauth1a, key, etc.
     */
    public function getAuthenticationType(): string
    {
        return 'none';
    }

    /**
     * Return array of key => label elements that will be converted to inputs to
     * obtain from the user.
     *
     * @return array<string, string>
     */
    public function getRequiredKeyFields(): array
    {
        // Do not rename field. fullcontact.js depends on it
        return [
            'apikey' => 'mailvotech.integration.fullcontact.apikey',
        ];
    }

    /**
     * @param FormBuilder|Form $builder
     * @param array            $data
     * @param string           $formArea
     */
    public function appendToForm(&$builder, $data, $formArea): void
    {
        if ('keys' === $formArea) {
            $builder->add(
                'test_api',
                ButtonType::class,
                [
                    'label' => 'mailvotech.plugin.fullcontact.test_api',
                    'attr'  => [
                        'class'   => 'btn btn-primary',
                        'style'   => 'margin-bottom: 10px',
                        'onclick' => 'MailVotech.testFullContactApi(this)',
                    ],
                ]
            );

            $builder->add(
                'stats',
                TextareaType::class,
                [
                    'label_attr' => ['class' => 'control-label'],
                    'label'      => 'mailvotech.plugin.fullcontact.stats',
                    'required'   => false,
                    'attr'       => [
                        'class'    => 'form-control',
                        'rows'     => '6',
                        'readonly' => 'readonly',
                    ],
                ]
            );

            $builder->add(
                'auto_update',
                YesNoButtonGroupType::class,
                [
                    'label' => 'mailvotech.plugin.fullcontact.auto_update',
                    'data'  => isset($data['auto_update']) && (bool) $data['auto_update'],
                    'attr'  => [
                        'tooltip' => 'mailvotech.plugin.fullcontact.auto_update.tooltip',
                    ],
                ]
            );
        }
    }

    public function shouldAutoUpdate(): bool
    {
        $featureSettings = $this->getKeys();

        return isset($featureSettings['auto_update']) && (bool) $featureSettings['auto_update'];
    }

    public function getFormNotes($section): array
    {
        if ('custom' === $section) {
            return [
                'template'   => '@MailVotechFullContact/Integration/form.html.twig',
                'parameters' => [
                    'mailvotechUrl' => $this->router->generate('mailvotech_plugin_fullcontact_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
                ],
            ];
        }

        return parent::getFormNotes($section);
    }
}
