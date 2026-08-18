<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\FormBundle\Entity\Form;

final class FormEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     */
    public function __construct(Form &$form, $isNew = false)
    {
        $this->entity = &$form;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Form entity.
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->entity;
    }

    /**
     * Sets the Form entity.
     */
    public function setForm(Form $form): void
    {
        $this->entity = $form;
    }
}
