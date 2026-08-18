<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Form\Type;

use MailVotech\CoreBundle\Helper\ArrayHelper;
use MailVotech\LeadBundle\Model\FieldModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
final class LeadFieldsType extends AbstractType
{
    public function __construct(
        private readonly FieldModel $fieldModel,
    ) {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'choices' => function (Options $options): array {
                $fieldList = ArrayHelper::flipArray($this->fieldModel->getFieldList());
                if ($options['with_tags']) {
                    $fieldList['Core']['mailvotech.lead.field.tags'] = 'tags';
                }
                if ($options['with_company_fields']) {
                    $fieldList['Company'] = array_flip($this->fieldModel->getFieldList(false, true, ['isPublished' => true, 'object' => 'company']));
                }
                if ($options['with_utm']) {
                    $fieldList['UTM']['mailvotech.lead.field.utmcampaign'] = 'utm_campaign';
                    $fieldList['UTM']['mailvotech.lead.field.utmcontent']  = 'utm_content';
                    $fieldList['UTM']['mailvotech.lead.field.utmmedium']   = 'utm_medium';
                    $fieldList['UTM']['mailvotech.lead.field.umtsource']   = 'utm_source';
                    $fieldList['UTM']['mailvotech.lead.field.utmterm']     = 'utm_term';
                }

                return $fieldList;
            },
            'global_only'         => false,
            'required'            => false,
            'with_company_fields' => false,
            'with_tags'           => false,
            'with_utm'            => false,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }

    public function getBlockPrefix(): string
    {
        return 'leadfields_choices';
    }
}
