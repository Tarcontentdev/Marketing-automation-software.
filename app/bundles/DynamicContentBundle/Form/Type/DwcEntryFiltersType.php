<?php

namespace MailVotech\DynamicContentBundle\Form\Type;

use MailVotech\LeadBundle\Form\Type\FilterTrait;
use MailVotech\LeadBundle\Model\ListModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<mixed>
 */
final class DwcEntryFiltersType extends AbstractType
{
    use FilterTrait;

    public function __construct(
        private TranslatorInterface $translator,
        private ListModel $listModel,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'glue',
            ChoiceType::class,
            [
                'label'   => false,
                'choices' => [
                    'mailvotech.lead.list.form.glue.and' => 'and',
                    'mailvotech.lead.list.form.glue.or'  => 'or',
                ],
                'attr'              => [
                    'class'    => 'form-control not-chosen glue-select',
                    'onchange' => 'MailVotech.updateFilterPositioning(this)',
                ],
            ]
        );

        $formModifier = function (FormEvent $event, $eventName): void {
            $this->buildFiltersForm($eventName, $event, $this->translator);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier): void {
                $formModifier($event, FormEvents::PRE_SET_DATA);
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($formModifier): void {
                $formModifier($event, FormEvents::PRE_SUBMIT);
            }
        );

        $builder->add('field', HiddenType::class);
        $builder->add('object', HiddenType::class);
        $builder->add('type', HiddenType::class);
    }

    /**
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(
            [
                'countries',
                'regions',
                'timezones',
                'locales',
                'fields',
                'deviceTypes',
                'deviceBrands',
                'deviceOs',
                'tags',
                'lists',
            ]
        );

        $resolver->setDefaults(
            [
                'label'          => false,
                'error_bubbling' => false,
                // @see \MailVotech\LeadBundle\Controller\AjaxController::loadSegmentFilterFormAction()
                'lists'          => $this->listModel->getChoiceFields()['lead']['leadlist']['properties']['list'],
            ]
        );
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['fields'] = $options['fields'];
    }
}
