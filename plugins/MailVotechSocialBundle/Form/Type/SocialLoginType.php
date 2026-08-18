<?php

namespace MailVotechPlugin\MailVotechSocialBundle\Form\Type;

use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\FormBundle\Model\FormModel;
use MailVotech\PluginBundle\Helper\IntegrationHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<array<mixed>>
 */
final class SocialLoginType extends AbstractType
{
    public function __construct(
        private readonly IntegrationHelper $helper,
        private readonly FormModel $formModel,
        private readonly CoreParametersHelper $coreParametersHelper,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $integrations       = '';
        $integrationObjects = $this->helper->getIntegrationObjects(null, 'login_button');
        foreach ($integrationObjects as $integrationObject) {
            if ($integrationObject->getIntegrationSettings()->isPublished()) {
                $model = $this->formModel;
                $integrations .= $integrationObject->getName().',';
                $integration = [
                    'integration' => $integrationObject->getName(),
                ];

                $builder->add(
                    'authUrl_'.$integrationObject->getName(),
                    HiddenType::class,
                    [
                        'data' => $model->buildUrl('mailvotech_integration_auth_user', $integration, true, []),
                    ]
                );

                $builder->add(
                    'buttonImageUrl',
                    HiddenType::class,
                    [
                        'data' => $this->coreParametersHelper->get('site_url').'/'.$this->coreParametersHelper->get('image_path').'/',
                    ]
                );
            }
        }

        $builder->add(
            'integrations',
            HiddenType::class,
            [
                'data' => $integrations,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'sociallogin';
    }
}
