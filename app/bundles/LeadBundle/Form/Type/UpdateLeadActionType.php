<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Form\Type;

use MailVotech\CoreBundle\Cache\ResultCacheOptions;
use MailVotech\LeadBundle\Entity\LeadField;
use MailVotech\LeadBundle\Model\FieldModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @extends AbstractType<mixed>
 */
final class UpdateLeadActionType extends AbstractType
{
    use EntityFieldsBuildFormTrait;

    public function __construct(
        private FieldModel $fieldModel,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $leadFields = $this->fieldModel->getEntities(
            [
                'force' => [
                    [
                        'column' => 'f.isPublished',
                        'expr'   => 'eq',
                        'value'  => true,
                    ],
                ],
                'hydration_mode' => 'HYDRATE_ARRAY',
                'result_cache'   => new ResultCacheOptions(LeadField::CACHE_NAMESPACE),
            ]
        );

        $options['fields']                      = $leadFields;
        $options['ignore_required_constraints'] = true;
        $options['ignore_date_type']            = true;
        $options['use_nullable_yes_no_type']    = true;

        $this->getFormFields($builder, $options);
    }

    public function getBlockPrefix(): string
    {
        return 'updatelead_action';
    }
}
