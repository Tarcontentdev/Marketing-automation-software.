<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Provider;

use Symfony\Component\Form\FormInterface;

interface FormAdjustmentsProviderInterface
{
    /**
     * Allows subscribers to adjust a form so new fields can be added, deleted or modified.
     *
     * @param FormInterface<FormInterface<mixed>> $form
     * @param mixed[]                             $fieldDetails
     *
     * @return FormInterface<FormInterface<mixed>>
     */
    public function adjustForm(FormInterface $form, string $fieldAlias, string $fieldObject, string $operator, array $fieldDetails): FormInterface;
}
