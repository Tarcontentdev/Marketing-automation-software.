<?php

namespace MailVotech\EmailBundle\Form\Type;

use MailVotech\CoreBundle\Form\Type\FormButtonsType;
use MailVotech\CoreBundle\Form\Type\LookupType;
use MailVotech\CoreBundle\Form\Type\SortableListType;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractType<mixed>
 */
final class ExampleSendType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly CorePermissions $security,
        private readonly UserHelper $userHelper,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'emails',
            SortableListType::class,
            [
                'entry_type'       => EmailType::class,
                'label'            => 'mailvotech.email.example_recipients',
                'add_value_button' => 'mailvotech.email.add_recipient',
                'option_notblank'  => false,
            ]
        );

        if ($this->security->isAdmin()
            || $this->security->hasEntityAccess(
                'lead:leads:viewown',
                'lead:leads:viewother',
                $this->userHelper->getUser()->getId()
            )) {
            $builder->add(
                'contact',
                LookupType::class,
                [
                    'attr' => [
                        'class'                => 'form-control',
                        'data-callback'        => 'activateExampleContactLookupField',
                        'data-toggle'          => 'field-lookup',
                        'data-lookup-callback' => 'updateExampleContactLookupListFilter',
                        'data-chosen-lookup'   => 'lead:contactList',
                        'placeholder'          => $this->translator->trans(
                            'mailvotech.lead.list.form.startTyping'
                        ),
                        'data-no-record-message' => $this->translator->trans(
                            'mailvotech.core.form.nomatches'
                        ),
                    ],
                    'required' => false,
                ]
            );

            $builder->add(
                'contact_id',
                HiddenType::class
            );
        }

        $builder->add(
            'buttons',
            FormButtonsType::class,
            [
                'apply_text' => false,
                'save_text'  => 'mailvotech.email.send',
                'save_icon'  => 'ri-send-plane-line',
            ]
        );
    }
}
