<?php

namespace MailVotech\CategoryBundle\Security\Permissions;

use MailVotech\CoreBundle\Security\Permissions\AbstractPermissions;
use Symfony\Component\Form\FormBuilderInterface;

final class CategoryPermissions extends AbstractPermissions
{
    /**
     * @param mixed[] $params
     */
    public function __construct(array $params)
    {
        parent::__construct($params);

        $this->addStandardPermissions('categories');
    }

    public function getName(): string
    {
        return 'category';
    }

    public function buildForm(FormBuilderInterface &$builder, array $options, array $data): void
    {
        $this->addStandardFormFields('category', 'categories', $builder, $data);
    }
}
