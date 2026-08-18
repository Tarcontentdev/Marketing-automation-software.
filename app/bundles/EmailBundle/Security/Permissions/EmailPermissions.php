<?php

namespace MailVotech\EmailBundle\Security\Permissions;

use MailVotech\CoreBundle\Security\Permissions\AbstractPermissions;
use MailVotech\UserBundle\Form\Type\PermissionListType;
use Symfony\Component\Form\FormBuilderInterface;

final class EmailPermissions extends AbstractPermissions
{
    /**
     * @param mixed[] $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        $this->addStandardPermissions('categories');
        $this->addExtendedPermissions('emails');
        $this->permissions['emails']['sendtodnc'] = 1;
    }

    public function getName(): string
    {
        return 'email';
    }

    public function buildForm(FormBuilderInterface &$builder, array $options, array $data): void
    {
        $this->addStandardFormFields('email', 'categories', $builder, $data);
        $this->addExtendedFormFields('email', 'emails', $builder, $data);
    }

    /**
     * Adds the standard permission set of viewown, viewother, editown, editother, create, deleteown, deleteother,
     * publishown, publishother and full to the form builder.
     *
     * @param string               $bundle
     * @param string               $level
     * @param FormBuilderInterface $builder
     * @param mixed[]              $data
     * @param bool                 $includePublish
     */
    protected function addExtendedFormFields($bundle, $level, &$builder, $data, $includePublish = true): void
    {
        $choices = [
            'mailvotech.core.permissions.viewown'     => 'viewown',
            'mailvotech.core.permissions.viewother'   => 'viewother',
            'mailvotech.core.permissions.editown'     => 'editown',
            'mailvotech.core.permissions.editother'   => 'editother',
            'mailvotech.core.permissions.create'      => 'create',
            'mailvotech.core.permissions.deleteown'   => 'deleteown',
            'mailvotech.core.permissions.deleteother' => 'deleteother',
            'mailvotech.core.permissions.full'        => 'full',
            'mailvotech.email.send.dnc.label'         => 'sendtodnc',
        ];

        if ($includePublish) {
            $choices['mailvotech.core.permissions.publishown']   = 'publishown';
            $choices['mailvotech.core.permissions.publishother'] = 'publishother';
        }

        $builder->add(
            "{$bundle}:{$level}",
            PermissionListType::class,
            [
                'choices'           => $choices,
                'choices_as_values' => true,
                'label'             => $this->getLabel($bundle, $level),
                'data'              => (!empty($data[$level]) ? $data[$level] : []),
                'bundle'            => $bundle,
                'level'             => $level,
            ]
        );
    }
}
