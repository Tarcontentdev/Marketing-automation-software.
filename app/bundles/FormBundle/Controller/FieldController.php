<?php

namespace MailVotech\FormBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use MailVotech\CoreBundle\Controller\FormController as CommonFormController;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Service\FlashBag;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\FormBundle\Collector\AlreadyMappedFieldCollectorInterface;
use MailVotech\FormBundle\Collector\MappedObjectCollectorInterface;
use MailVotech\FormBundle\Entity\Field;
use MailVotech\FormBundle\Event\FormBuilderEvent;
use MailVotech\FormBundle\FormEvents;
use MailVotech\FormBundle\Helper\FormFieldHelper;
use MailVotech\FormBundle\Model\FieldModel;
use MailVotech\FormBundle\Model\FormModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final class FieldController extends CommonFormController
{
    public function __construct(
        private readonly FormModel $formModel,
        private readonly FieldModel $formFieldModel,
        FormFieldHelper $fieldHelper,
        FormFactoryInterface $formFactory,
        private readonly MappedObjectCollectorInterface $mappedObjectCollector,
        private readonly AlreadyMappedFieldCollectorInterface $alreadyMappedFieldCollector,
        ManagerRegistry $doctrine,
        ModelFactory $modelFactory,
        UserHelper $userHelper,
        CoreParametersHelper $coreParametersHelper,
        EventDispatcherInterface $dispatcher,
        Translator $translator,
        FlashBag $flashBag,
        RequestStack $requestStack,
        CorePermissions $security,
    ) {
        $this->fieldHelper                 = $fieldHelper;
        $this->formFactory                 = $formFactory;

        parent::__construct($formFactory, $fieldHelper, $doctrine, $modelFactory, $userHelper, $coreParametersHelper, $dispatcher, $translator, $flashBag, $requestStack, $security);
    }

    /**
     * Generates new form and processes post data.
     */
    public function newAction(Request $request, Environment $twig): JsonResponse|Response
    {
        $success = 0;
        $valid   = $cancelled   = false;
        $method  = $request->getMethod();
        $session = $request->getSession();

        if ('POST' === $method) {
            $formField = $request->request->all()['formfield'] ?? [];
            $fieldType = $formField['type'];
            $formId    = $formField['formId'];
        } else {
            $fieldType = $request->query->get('type');
            $formId    = $request->query->get('formId');
            $formField = [
                'type'     => $fieldType,
                'formId'   => $formId,
                'parent'   => $request->query->get('parent'),
            ];
        }

        $customComponents = $this->formModel->getCustomComponents();
        $customParams     = $customComponents['fields'][$fieldType] ?? false;
        // ajax only for form fields
        if (!$fieldType
            || !$request->isXmlHttpRequest()
            || !$this->security->isGranted(['form:forms:editown', 'form:forms:editother', 'form:forms:create'], 'MATCH_ONE')
        ) {
            return $this->modalAccessDenied();
        }

        // Generate the form
        $form = $this->getFieldForm($formId, $formField);

        if (!empty($customParams)) {
            $formField['isCustom']         = true;
            $formField['customParameters'] = $customParams;
        }

        // Check for a submitted form and process it
        if ('POST' === $method) {
            if (!$cancelled = $this->isFormCancelled($form)) {
                if ($valid = $this->isFormValid($form)) {
                    $success = 1;

                    // form is valid so process the data
                    $keyId = 'new'.hash('sha1', uniqid(mt_rand()));

                    // save the properties to session
                    $fields          = $session->get('mailvotech.form.'.$formId.'.fields.modified', []);
                    $formData        = $form->getData();
                    $formField       = array_merge($formField, $formData);
                    $formField['id'] = $keyId;

                    // Get aliases in order to generate a new one for the new field
                    $aliases = [];
                    foreach ($fields as $f) {
                        $aliases[] = $f['alias'];
                    }

                    // Generate or ensure a unique alias
                    $alias          = empty($formField['alias']) ? $formField['label'] : $formField['alias'];
                    $formField['alias'] = $this->formFieldModel->generateAlias($alias, $aliases);

                    // Force required for captcha if not a honeypot
                    if ('captcha' == $formField['type']) {
                        $formField['isRequired'] = !empty($formField['properties']['captcha']);
                    }

                    // Add field before the submit button
                    if (count($fields)) {
                        $submitKey   = null;
                        $submitField = null;

                        foreach ($fields as $key => $field) {
                            if (isset($field['type']) && 'button' === $field['type']) {
                                $submitKey   = $key;
                                $submitField = $field;
                                break;
                            }
                        }

                        if ($submitKey) {
                            // Remove submit button, add new field, re-add submit button at the end
                            unset($fields[$submitKey]);
                            $fields[$keyId]     = $formField;
                            $fields[$submitKey] = $submitField;
                        } else {
                            $fields[$keyId] = $formField;
                        }
                    } else {
                        $fields[$keyId] = $formField;
                    }

                    $session->set('mailvotech.form.'.$formId.'.fields.modified', $fields);

                    // Keep track of used lead fields
                    if (!empty($formField['mappedObject']) && !empty($formField['mappedField']) && empty($formData['parent'])) {
                        $this->alreadyMappedFieldCollector->addField($formId, $formField['mappedObject'], $formField['mappedField']);
                    }
                } else {
                    $success = 0;
                }
            }
        }

        $viewParams = ['type' => $fieldType];
        if ($cancelled || $valid) {
            $closeModal = true;
        } else {
            $closeModal                = false;
            $viewParams['tmpl']        = 'field';
            $viewParams['form']        = (isset($customParams['formTheme'])) ? $this->setFormTheme($form, $twig, $customParams['formTheme']) : $form->createView();
            $viewParams['fieldHeader'] = (!empty($customParams)) ? $this->translator->trans($customParams['label']) : $this->translator->transConditional('mailvotech.core.type.'.$fieldType, 'mailvotech.form.field.type.'.$fieldType);
        }

        $passthroughVars = [
            'mailvotechContent' => 'formField',
            'success'       => $success,
            'route'         => false,
        ];

        if (!empty($keyId)) {
            $entity     = new Field();
            $blank      = $entity->convertToArray();
            $formField  = array_merge($blank, $formField);
            $formEntity = $this->formModel->getEntity($formId);

            $passthroughVars['parent']    = $formField['parent'];
            $passthroughVars['fieldId']   = $keyId;
            $template                     = (!empty($customParams)) ? $customParams['template'] : '@MailVotechForm/Field/'.$fieldType.'.html.twig';
            $passthroughVars['fieldHtml'] = $this->renderView(
                '@MailVotechForm/Builder/_field_wrapper.html.twig',
                [
                    'isConditional'        => !empty($formField['parent']),
                    'template'             => $template,
                    'inForm'               => true,
                    'field'                => $formField,
                    'id'                   => $keyId,
                    'formId'               => $formId,
                    'formName'             => null === $formEntity ? 'newform' : $formEntity->generateFormName(),
                    'mappedFields'         => $this->mappedObjectCollector->buildCollection((string) $formField['mappedObject']),
                    'inBuilder'            => true,
                    'fields'               => $this->fieldHelper->getChoiceList($customComponents['fields']),
                    'viewOnlyFields'       => $customComponents['viewOnlyFields'],
                    'formFields'           => $fields,
                ]
            );
        }

        if ($closeModal) {
            // just close the modal
            $passthroughVars['closeModal'] = 1;

            return new JsonResponse($passthroughVars);
        }

        return $this->ajaxAction($request, [
            'contentTemplate' => '@MailVotechForm/Builder/'.$viewParams['tmpl'].'.html.twig',
            'viewParameters'  => $viewParams,
            'passthroughVars' => $passthroughVars,
        ]);
    }

    /**
     * Generates edit form and processes post data.
     *
     * @param int $objectId
     */
    public function editAction(Request $request, Environment $twig, $objectId): JsonResponse|Response
    {
        $session   = $request->getSession();
        $method    = $request->getMethod();
        $formfield = $request->request->all()['formfield'] ?? [];
        $formId    = 'POST' === $method ? ($formfield['formId'] ?? '') : $request->query->get('formId');
        $fields    = $session->get('mailvotech.form.'.$formId.'.fields.modified', []);
        $success   = 0;
        $valid     = $cancelled = false;
        $formField = array_key_exists($objectId, $fields) ? $fields[$objectId] : [];

        if ($formField) {
            $fieldType = $formField['type'];

            // ajax only for form fields
            if (!$fieldType
                || !$request->isXmlHttpRequest()
                || !$this->security->isGranted(['form:forms:editown', 'form:forms:editother', 'form:forms:create'], 'MATCH_ONE')
            ) {
                return $this->modalAccessDenied();
            }

            // Generate the form
            $form = $this->getFieldForm($formId, $formField);

            // Check for a submitted form and process it
            if ('POST' === $method) {
                if (!$cancelled = $this->isFormCancelled($form)) {
                    if ($valid = $this->isFormValid($form)) {
                        $success = 1;

                        // form is valid so process the data

                        // save the properties to session
                        $session  = $request->getSession();
                        $fields   = $session->get('mailvotech.form.'.$formId.'.fields.modified');
                        $formData = $form->getData();

                        // overwrite with updated data
                        $formField = array_merge($fields[$objectId], $formData);

                        if (str_contains((string) $objectId, 'new')) {
                            // Get aliases in order to generate update for this one
                            $aliases = [];
                            foreach ($fields as $k => $f) {
                                if ($k != $objectId) {
                                    $aliases[] = $f['alias'];
                                }
                            }
                            $formField['alias'] = $this->formFieldModel->generateAlias(
                                $formField['alias'] ?? $formField['label'] ?? '',
                                $aliases
                            );
                        }

                        // Force required for captcha if not a honeypot
                        if ('captcha' == $formField['type']) {
                            $formField['isRequired'] = !empty($formField['properties']['captcha']);
                        }

                        $fields[$objectId] = $formField;
                        $session->set('mailvotech.form.'.$formId.'.fields.modified', $fields);

                        // Keep track of used lead fields
                        if (!empty($formField['mappedObject']) && !empty($formField['mappedField']) && empty($formData['parent'])) {
                            $this->alreadyMappedFieldCollector->addField($formId, $formField['mappedObject'], $formField['mappedField']);
                        }
                    }
                }
            }

            $viewParams       = ['type' => $fieldType];
            $customComponents = $this->formModel->getCustomComponents();
            $customParams     = $customComponents['fields'][$fieldType] ?? false;

            if ($cancelled || $valid) {
                $closeModal = true;
            } else {
                $closeModal         = false;
                $viewParams['tmpl'] = 'field';
                $viewParams['form'] = (isset($customParams['formTheme'])) ? $this->setFormTheme(
                    $form,
                    $twig,
                    $customParams['formTheme']
                ) : $form->createView();
                $viewParams['fieldHeader'] = (!empty($customParams))
                    ? $this->translator->trans($customParams['label'])
                    : $this->translator->transConditional('mailvotech.core.type.'.$fieldType, 'mailvotech.form.field.type.'.$fieldType);
            }

            $passthroughVars = [
                'mailvotechContent' => 'formField',
                'success'       => $success,
                'route'         => false,
            ];

            $passthroughVars['fieldId'] = $objectId;
            $template                   = (!empty($customParams)) ? $customParams['template'] : '@MailVotechForm/Field/'.$fieldType.'.html.twig';

            // prevent undefined errors
            $entity       = new Field();
            $blank        = $entity->convertToArray();
            $formField    = array_merge($blank, $formField);

            $passthroughVars['fieldHtml'] = $this->renderView(
                '@MailVotechForm/Builder/_field_wrapper.html.twig',
                [
                    'isConditional'        => !empty($formField['parent']),
                    'template'             => $template,
                    'inForm'               => true,
                    'field'                => $formField,
                    'id'                   => $objectId,
                    'formId'               => $formId,
                    'mappedFields'         => $this->mappedObjectCollector->buildCollection((string) $formField['mappedObject']),
                    'inBuilder'            => true,
                    'fields'               => $this->fieldHelper->getChoiceList($customComponents['fields']),
                    'formFields'           => $fields,
                    'viewOnlyFields'       => $customComponents['viewOnlyFields'],
                ]
            );

            if ($closeModal) {
                // just close the modal
                $passthroughVars['closeModal'] = 1;

                return new JsonResponse($passthroughVars);
            }

            return $this->ajaxAction(
                $request,
                [
                    'contentTemplate' => '@MailVotechForm/Builder/'.$viewParams['tmpl'].'.html.twig',
                    'viewParameters'  => $viewParams,
                    'passthroughVars' => $passthroughVars,
                ]
            );
        }

        return new JsonResponse(['success' => 0]);
    }

    /**
     * Deletes the entity.
     *
     * @param int $objectId
     */
    public function deleteAction(Request $request, $objectId): JsonResponse
    {
        $session = $request->getSession();
        $formId  = $request->query->get('formId');
        $fields  = $session->get('mailvotech.form.'.$formId.'.fields.modified', []);
        $delete  = $session->get('mailvotech.form.'.$formId.'.fields.deleted', []);

        // ajax only for form fields
        if (!$request->isXmlHttpRequest()
            || !$this->security->isGranted(['form:forms:editown', 'form:forms:editother', 'form:forms:create'], 'MATCH_ONE')
        ) {
            $this->throwAccessDenied();
        }

        $formField = $fields[$objectId] ?? null;

        if ('POST' === $request->getMethod() && null !== $formField) {
            if ($formField['mappedObject'] && $formField['mappedField']) {
                // Allow to select the lead field from the delete field again
                $this->alreadyMappedFieldCollector->removeField($formId, $formField['mappedObject'], $formField['mappedField']);
            }

            // add the field to the delete list
            if (!in_array($objectId, $delete)) {
                $delete[] = $objectId;
                $session->set('mailvotech.form.'.$formId.'.fields.deleted', $delete);
            }

            $dataArray = [
                'mailvotechContent' => 'formField',
                'success'       => 1,
                'route'         => false,
            ];
        } else {
            $dataArray = ['success' => 0];
        }

        return new JsonResponse($dataArray);
    }

    /**
     * @param int     $formId
     * @param mixed[] $formField
     *
     * @return mixed
     */
    private function getFieldForm($formId, array $formField)
    {
        $customComponents = $this->formModel->getCustomComponents();
        $customParams     = $customComponents['fields'][$formField['type']] ?? false;
        $form = $this->formFieldModel->createForm(
            $formField,
            $this->formFactory,
            (!empty($formField['id'])) ?
                $this->generateUrl('mailvotech_formfield_action', ['objectAction' => 'edit', 'objectId' => $formField['id']])
                : $this->generateUrl('mailvotech_formfield_action', ['objectAction' => 'new']),
            ['customParameters' => $customParams]
        );
        $form->get('formId')->setData($formId);

        $event      = new FormBuilderEvent($this->translator);
        $this->dispatcher->dispatch($event, FormEvents::FORM_ON_BUILD);
        $event->addValidatorsToBuilder($form);

        return $form;
    }
}
