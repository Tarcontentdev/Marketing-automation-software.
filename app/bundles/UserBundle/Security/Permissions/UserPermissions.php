<?php

namespace MailVotech\UserBundle\Security\Permissions;

use MailVotech\CoreBundle\Security\Permissions\AbstractPermissions;
use MailVotech\UserBundle\Form\Type\PermissionListType;
use Symfony\Component\Form\FormBuilderInterface;

final class UserPermissions extends AbstractPermissions
{
    /**
     * @param mixed[] $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params);
        $this->permissions = [
            'profile' => [
                'editusername' => 1,
                'editemail'    => 2,
                'editposition' => 4,
                'editname'     => 8,
                'full'         => 1024,
            ],
        ];
        $this->addStandardPermissions('users', false);
        $this->addStandardPermissions('roles', false);
    }

    public function getName(): string
    {
        return 'user';
    }

    public function buildForm(FormBuilderInterface &$builder, array $options, array $data): void
    {
        $this->addStandardFormFields('user', 'users', $builder, $data, false);
        $this->addStandardFormFields('user', 'roles', $builder, $data, false);

        $builder->add(
            'user:profile',
            PermissionListType::class,
            [
                'choices'           => [
                    'mailvotech.user.account.permissions.editname'     => 'editname',
                    'mailvotech.user.account.permissions.editusername' => 'editusername',
                    'mailvotech.user.account.permissions.editemail'    => 'editemail',
                    'mailvotech.user.account.permissions.editposition' => 'editposition',
                    'mailvotech.user.account.permissions.editall'      => 'full',
                ],
                'label'  => 'mailvotech.user.permissions.profile',
                'data'   => (!empty($data['profile']) ? $data['profile'] : []),
                'bundle' => 'user',
                'level'  => 'profile',
            ]
        );
    }
}
