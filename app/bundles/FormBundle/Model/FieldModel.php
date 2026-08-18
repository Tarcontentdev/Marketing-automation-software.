<?php

namespace MailVotech\FormBundle\Model;

use MailVotech\CoreBundle\Doctrine\Helper\ColumnSchemaHelper;
use MailVotech\CoreBundle\Model\FormModel as CommonFormModel;
use MailVotech\FormBundle\Entity\Field;
use MailVotech\FormBundle\Entity\FieldRepository;
use MailVotech\FormBundle\Event\FormFieldEvent;
use MailVotech\FormBundle\Form\Type\FieldType;
use MailVotech\FormBundle\FormEvents;
use MailVotech\LeadBundle\Model\FieldModel as LeadFieldModel;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @extends CommonFormModel<Field>
 */
class FieldModel extends CommonFormModel
{
    protected LeadFieldModel $leadFieldModel;

    private RequestStack $requestStack;

    private ColumnSchemaHelper $columnSchemaHelper;

    private FieldRepository $fieldRepository;

    #[Required]
    public function autowireFieldModel(
        LeadFieldModel $leadFieldModel,
        RequestStack $requestStack,
        ColumnSchemaHelper $columnSchemaHelper,
        FieldRepository $fieldRepository,
    ): void {
        $this->leadFieldModel     = $leadFieldModel;
        $this->requestStack       = $requestStack;
        $this->columnSchemaHelper = $columnSchemaHelper;
        $this->fieldRepository    = $fieldRepository;
    }

    private function getSession(): SessionInterface
    {
        return $this->requestStack->getSession();
    }

    /**
     * @param object|array<mixed> $entity
     * @param string|null         $action
     * @param array               $options
     *
     * @return FormInterface<mixed>
     */
    public function createForm($entity, FormFactoryInterface $formFactory, $action = null, $options = []): FormInterface
    {
        if ($action) {
            $options['action'] = $action;
        }

        return $formFactory->create(FieldType::class, $entity, $options);
    }

    public function getRepository(): FieldRepository
    {
        return $this->fieldRepository;
    }

    public function getPermissionBase(): string
    {
        return 'form:forms';
    }

    public function getEntity($id = null): ?Field
    {
        if (null === $id) {
            return new Field();
        }

        return parent::getEntity($id);
    }

    /**
     * Get the fields saved in session.
     */
    public function getSessionFields($formId): array
    {
        $fields = $this->getSession()->get('mailvotech.form.'.$formId.'.fields.modified', []);
        $remove = $this->getSession()->get('mailvotech.form.'.$formId.'.fields.deleted', []);

        return array_diff_key($fields, array_flip($remove));
    }

    /**
     * @param string[] $aliases
     */
    public function generateAlias(string $label, array &$aliases): string
    {
        $alias = $this->cleanAlias($label, 'f_', 25);

        // make sure alias is not already taken
        $testAlias = $alias;

        $count    = (int) in_array($alias, $aliases);
        $aliasTag = $count;

        while ($count) {
            $testAlias = $alias.$aliasTag;
            $count     = (int) in_array($testAlias, $aliases);
            ++$aliasTag;
        }

        // Prevent internally used identifiers in the form HTML from colliding with the generated field's ID
        $internalUse = ['message', 'error', 'id', 'return', 'name', 'messenger'];
        if (in_array($testAlias, $internalUse)) {
            $testAlias = 'f_'.$testAlias;
        }

        $aliases[] = $testAlias;

        return $testAlias;
    }

    /**
     * @throws MethodNotAllowedHttpException
     */
    protected function dispatchEvent($action, &$entity, $isNew = false, ?Event $event = null): ?Event
    {
        if (!$entity instanceof Field) {
            throw new MethodNotAllowedHttpException(['Form']);
        }

        switch ($action) {
            case 'pre_save':
                $name = FormEvents::FIELD_PRE_SAVE;
                break;
            case 'post_save':
                $name = FormEvents::FIELD_POST_SAVE;
                break;
            case 'pre_delete':
                $name = FormEvents::FIELD_PRE_DELETE;
                break;
            case 'post_delete':
                $name = FormEvents::FIELD_POST_DELETE;
                break;
            default:
                return null;
        }

        if ($this->dispatcher->hasListeners($name)) {
            if (!$event instanceof Event) {
                $event = new FormFieldEvent($entity, $isNew);
            }

            $this->dispatcher->dispatch($event, $name);

            return $event;
        }

        return null;
    }

    /**
     * Updates the table structure for form results.
     */
    public function removeFieldColumn(Field $field): void
    {
        $form = $field->getForm();

        $name = 'form_results_'.$form->getId().'_'.$form->getAlias();

        $schemaHelper = $this->columnSchemaHelper->setName($name);
        $schemaHelper->dropColumn($field->getAlias());
        $schemaHelper->executeChanges();
    }
}
