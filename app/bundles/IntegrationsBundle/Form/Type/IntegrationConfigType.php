<?php

declare(strict_types=1);

namespace MailVotech\IntegrationsBundle\Form\Type;

use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\YesNoButtonGroupType;
use MailVotech\IntegrationsBundle\Exception\IntegrationNotFoundException;
use MailVotech\IntegrationsBundle\Helper\ConfigIntegrationsHelper;
use MailVotech\IntegrationsBundle\Integration\Interfaces\ConfigFormAuthInterface;
use MailVotech\IntegrationsBundle\Integration\Interfaces\ConfigFormFeaturesInterface;
use MailVotech\PluginBundle\Entity\Integration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<Integration>
 */
final class IntegrationConfigType extends AbstractType
{
    public function __construct(
        private readonly ConfigIntegrationsHelper $integrationsHelper,
    ) {
    }

    /**
     * @throws IntegrationNotFoundException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $integrationObject = $this->integrationsHelper->getIntegration($options['integration']);

        // isPublished
        $builder->add(
            'isPublished',
            YesNoButtonGroupType::class,
            [
                'label'      => 'mailvotech.integration.enabled',
                'label_attr' => ['class' => 'control-label'],
            ]
        );

        // apiKeys
        if ($integrationObject instanceof ConfigFormAuthInterface) {
            $builder->add(
                'apiKeys',
                $integrationObject->getAuthConfigFormName(),
                [
                    'label'       => false,
                    'integration' => $integrationObject,
                ]
            );
        }

        // supportedFeatures
        if ($integrationObject instanceof ConfigFormFeaturesInterface) {
            // @todo add tooltip support
            $builder->add(
                'supportedFeatures',
                ChoiceType::class,
                [
                    'label'      => 'mailvotech.integration.features',
                    'label_attr' => ['class' => 'control-label'],
                    'choices'    => array_flip($integrationObject->getSupportedFeatures()),
                    'expanded'   => true,
                    'multiple'   => true,
                    'required'   => false,
                ]
            );
        }

        // featureSettings
        $builder->add(
            'featureSettings',
            IntegrationFeatureSettingsType::class,
            [
                'label'             => false,
                'integrationObject' => $integrationObject,
            ]
        );

        $builder->add('buttons', FormButtonsType::class);

        $builder->setAction($options['action']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(
            [
                'integration',
            ]
        );

        $resolver->setDefined(
            [
                'data_class'  => Integration::class,
            ]
        );
    }
}
