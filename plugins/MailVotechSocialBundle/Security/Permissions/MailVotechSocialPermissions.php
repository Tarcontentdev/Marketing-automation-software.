<?php

namespace MailVotechPlugin\MailVotechSocialBundle\Security\Permissions;

use MailVotech\CoreBundle\Security\Permissions\AbstractPermissions;
use Symfony\Component\Form\FormBuilderInterface;

final class MailVotechSocialPermissions extends AbstractPermissions
{
    /**
     * @param mixed[] $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params);
        $this->addStandardPermissions('categories');
        $this->addStandardPermissions('monitoring');
        $this->addExtendedPermissions('tweets');
    }

    public function getName(): string
    {
        return 'mailvotechSocial';
    }

    public function buildForm(FormBuilderInterface &$builder, array $options, array $data): void
    {
        $this->addStandardFormFields('mailvotechSocial', 'categories', $builder, $data);
        $this->addStandardFormFields('mailvotechSocial', 'monitoring', $builder, $data);
        $this->addExtendedFormFields('mailvotechSocial', 'tweets', $builder, $data);
    }
}
