<?php

namespace MailVotech\CoreBundle\Form\Type;

use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @extends AbstractType<mixed>
 */
final class DateRangeType extends AbstractType
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly CoreParametersHelper $coreParametersHelper,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $session         = $this->requestStack->getSession();
        $humanFormat     = 'M j, Y';
        $sessionDateFrom = $session->get('mailvotech.daterange.form.from');
        $sessionDateTo   = $session->get('mailvotech.daterange.form.to');
        if (!empty($sessionDateFrom) && !empty($sessionDateTo)) {
            $defaultFrom = new \DateTime($sessionDateFrom);
            $defaultTo   = new \DateTime($sessionDateTo);
        } else {
            $dateRangeDefault = $this->coreParametersHelper->get('default_daterange_filter', '-1 month');
            $defaultFrom      = new \DateTime($dateRangeDefault);
            $defaultTo        = new \DateTime();
        }

        $dateFrom = (empty($options['data']['date_from']))
            ?
            $defaultFrom
            :
            new \DateTime($options['data']['date_from']);

        $builder->add(
            'date_from',
            TextType::class,
            [
                'label'      => 'mailvotech.core.date.from',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => false,
                'data'       => $dateFrom->format($humanFormat),
            ]
        );

        $dateTo = (empty($options['data']['date_to']))
            ?
            $defaultTo
            :
            new \DateTime($options['data']['date_to']);

        $builder->add(
            'date_to',
            TextType::class,
            [
                'label'      => 'mailvotech.core.date.to',
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
                'required'   => false,
                'data'       => $dateTo->format($humanFormat),
            ]
        );

        $builder->add(
            'apply',
            SubmitType::class,
            [
                'label' => 'mailvotech.core.form.apply',
                'attr'  => ['class' => 'btn btn-ghost'],
            ]
        );

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }

        $session->set('mailvotech.daterange.form.from', $dateFrom->format($humanFormat));
        $session->set('mailvotech.daterange.form.to', $dateTo->format($humanFormat));
    }

    public function getBlockPrefix(): string
    {
        return 'daterange';
    }
}
