<?php

namespace MailVotech\LeadBundle\Form\Type;

use MailVotech\CoreBundle\Form\EventListener\CleanFormSubscriber;
use MailVotech\CoreBundle\Form\EventListener\FormExitSubscriber;
use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Helper\DateTimeHelper;
use MailVotech\LeadBundle\Entity\LeadNote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<LeadNote>
 */
final class NoteType extends AbstractType
{
    private readonly DateTimeHelper $dateHelper;

    public function __construct()
    {
        $this->dateHelper = new DateTimeHelper();
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new CleanFormSubscriber(['text' => 'html']));
        $builder->addEventSubscriber(new FormExitSubscriber('lead.note', $options));

        $builder->add(
            'text',
            TextareaType::class,
            [
                'label'      => 'mailvotech.lead.note.form.text',
                'label_attr' => ['class' => 'control-label sr-only'],
                'attr'       => ['class' => 'mousetrap form-control editor', 'rows' => 10, 'autofocus' => 'autofocus'],
            ]
        );

        $builder->add(
            'type',
            ChoiceType::class,
            [
                'label'             => 'mailvotech.lead.note.form.type',
                'choices'           => [
                    'mailvotech.lead.note.type.general' => 'general',
                    'mailvotech.lead.note.type.email'   => 'email',
                    'mailvotech.lead.note.type.call'    => 'call',
                    'mailvotech.lead.note.type.meeting' => 'meeting',
                ],
                'label_attr' => ['class' => 'control-label'],
                'attr'       => ['class' => 'form-control'],
            ]
        );

        $dt   = $options['data']->getDatetime();
        $data = (null == $dt) ? $this->dateHelper->getDateTime() : $dt;

        $builder->add(
            'dateTime',
            DateTimeType::class,
            [
                'label'      => 'mailvotech.core.date.added',
                'label_attr' => ['class' => 'control-label'],
                'widget'     => 'single_text',
                'attr'       => [
                    'class'       => 'form-control',
                    'data-toggle' => 'datetime',
                    'preaddon'    => 'ri-calendar-line',
                ],
                'format' => 'yyyy-MM-dd HH:mm',
                'html5'  => false,
                'data'   => $data,
            ]
        );

        $builder->add('buttons', FormButtonsType::class, [
            'apply_text' => false,
            'save_text'  => 'mailvotech.core.form.save',
        ]);

        if (!empty($options['action'])) {
            $builder->setAction($options['action']);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => LeadNote::class,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'leadnote';
    }
}
