<?php

namespace MailVotech\CoreBundle\Form\Type;

use MailVotech\IntegrationsBundle\Exception\IntegrationNotFoundException;
use MailVotech\IntegrationsBundle\Helper\BuilderIntegrationsHelper;
use MailVotech\LeadBundle\Helper\FormFieldHelper;
use MailVotech\LeadBundle\Model\ListModel;
use MailVotech\StageBundle\Entity\StageRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class DynamicContentFilterEntryType extends AbstractType
{
    /**
     * @var mixed[]
     */
    private array $fieldChoices = [];

    /**
     * @var mixed[]
     */
    private readonly array $countryChoices;

    /**
     * @var mixed[]
     */
    private readonly array $regionChoices;

    /**
     * @var mixed[]
     */
    private readonly array $timezoneChoices;

    /**
     * @var mixed[]
     */
    private readonly array $localeChoices;

    public function __construct(
        ListModel $listModel,
        private readonly BuilderIntegrationsHelper $builderIntegrationsHelper,
        private readonly StageRepository $stageRepository,
    ) {
        $this->fieldChoices = $listModel->getChoiceFields();

        $this->filterFieldChoices();

        $this->countryChoices            = FormFieldHelper::getCountryChoices();
        $this->regionChoices             = FormFieldHelper::getRegionChoices();
        $this->timezoneChoices           = FormFieldHelper::getTimezonesChoices();
        $this->localeChoices             = FormFieldHelper::getLocaleChoices();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $extraClasses = '';

        try {
            $mailvotechBuilder = $this->builderIntegrationsHelper->getBuilder('email');
            $mailvotechBuilder->getName();
        } catch (IntegrationNotFoundException) {
            // Assume legacy builder
            $extraClasses = ' legacy-builder';
        }

        $builder->add(
            'content',
            TextareaType::class,
            [
                'label' => 'mailvotech.core.dynamicContent.alt_content',
                'attr'  => [
                    'class' => 'form-control editor editor-dynamic-content'.$extraClasses,
                ],
            ]
        );

        $builder->add(
            $builder->create(
                'filters',
                CollectionType::class,
                [
                    'entry_type'    => DynamicContentFilterEntryFiltersType::class,
                    'entry_options' => [
                        'label' => false,
                        'attr'  => [
                            'class' => 'form-control',
                        ],
                        'countries' => $this->countryChoices,
                        'regions'   => $this->regionChoices,
                        'timezones' => $this->timezoneChoices,
                        'stages'    => $this->getStageList(),
                        'locales'   => $this->localeChoices,
                        'fields'    => $this->fieldChoices,
                    ],
                    'error_bubbling' => false,
                    'mapped'         => true,
                    'allow_add'      => true,
                    'allow_delete'   => true,
                    'label'          => false,
                ]
            )
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['fields'] = $this->fieldChoices;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'label'          => false,
                'error_bubbling' => false,
            ]
        );
    }

    private function filterFieldChoices(): void
    {
        $this->fieldChoices['lead'] = array_filter(
            $this->fieldChoices['lead'],
            fn ($key): bool => !in_array(
                $key,
                [
                    'company',
                    'campaign',
                    'device_type',
                    'device_brand',
                    'device_os',
                    'lead_email_received',
                    'tags',
                    'dnc_bounced',
                    'dnc_unsubscribed',
                    'dnc_bounced_sms',
                    'dnc_unsubscribed_sms',
                    'hit_url',
                ]
            ),
            ARRAY_FILTER_USE_KEY
        );
    }

    private function getStageList(): array
    {
        $stages = $this->stageRepository->getSimpleList();

        foreach ($stages as $stage) {
            $stages[$stage['value']] = $stage['label'];
        }

        return $stages;
    }
}
