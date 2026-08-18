<?php

namespace MailVotech\DashboardBundle\Form\Type;

use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\DashboardBundle\DashboardEvents;
use MailVotech\DashboardBundle\Event\WidgetFormEvent;
use MailVotech\DashboardBundle\Event\WidgetTypeListEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @extends AbstractType<mixed>
 */
final class WidgetType extends AbstractType
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly CorePermissions $security,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'label'      => 'mailvotech.dashboard.widget.form.name',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control form-control-widget'],
                'required'   => false,
            ]
        );

        $event = new WidgetTypeListEvent();
        $event->setSecurity($this->security);
        $this->dispatcher->dispatch($event, DashboardEvents::DASHBOARD_ON_MODULE_LIST_GENERATE);

        $types = array_map(array_flip(...), $event->getTypes());

        $builder->add(
            'type',
            ChoiceType::class,
            [
                'label'             => 'mailvotech.dashboard.widget.form.type',
                'choices'           => $types,
                'label_attr'        => ['class' => 'control-label'],
                'placeholder'       => 'mailvotech.core.select',
                'attr'              => [
                    'class'    => 'form-control form-control-widget',
                    'onchange' => 'MailVotech.updateWidgetForm(this)',
                ],
            ]
        );

        $builder->add(
            'width',
            ChoiceType::class,
            [
                'label'   => 'mailvotech.dashboard.widget.form.width',
                'choices' => [
                    '25%'  => '25',
                    '50%'  => '50',
                    '75%'  => '75',
                    '100%' => '100',
                ],
                'empty_data'        => '100',
                'label_attr'        => ['class' => 'control-label'],
                'attr'              => ['class' => 'form-control form-control-widget'],
                'required'          => false,
            ]
        );

        $builder->add(
            'height',
            ChoiceType::class,
            [
                'label'   => 'mailvotech.dashboard.widget.form.height',
                'choices' => [
                    'mailvotech.dashboard.widget.size.extra_small' => '215',
                    'mailvotech.dashboard.widget.size.small'       => '330',
                    'mailvotech.dashboard.widget.size.medium'      => '445',
                    'mailvotech.dashboard.widget.size.large'       => '560',
                    'mailvotech.dashboard.widget.size.extra_large' => '675',
                ],
                'empty_data'        => '330',
                'label_attr'        => ['class' => 'control-label'],
                'attr'              => ['class' => 'form-control form-control-widget'],
                'required'          => false,
            ]
        );

        // function to add a form for specific widget type dynamically
        $func = function (FormEvent $e): void {
            $data   = $e->getData();
            $form   = $e->getForm();
            $event  = new WidgetFormEvent();
            $type   = null;
            $params = [];

            // $data is object on load, array on save (??)
            if (is_array($data)) {
                if (isset($data['type'])) {
                    $type = $data['type'];
                }
                if (isset($data['params'])) {
                    $params = $data['params'];
                }
            } else {
                $type   = $data->getType();
                $params = $data->getParams();
            }

            $event->setType($type);
            $this->dispatcher->dispatch($event, DashboardEvents::DASHBOARD_ON_MODULE_FORM_GENERATE);
            $widgetForm = $event->getForm();
            $form->setData($params);

            if (isset($widgetForm['formAlias'])) {
                $form->add('params', $widgetForm['formAlias'], [
                    'label' => false,
                ]);
            }
        };

        $builder->add(
            'id',
            HiddenType::class,
            [
                'mapped' => false,
            ]
        );

        $builder->add(
            'buttons',
            FormButtonsType::class,
            [
                'apply_text' => false,
                'save_text'  => 'mailvotech.core.form.save',
            ]
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }

        // Register the function above as EventListener on PreSet and PreBind
        $builder->addEventListener(FormEvents::PRE_SET_DATA, $func);
        $builder->addEventListener(FormEvents::PRE_SUBMIT, $func);
    }
}
