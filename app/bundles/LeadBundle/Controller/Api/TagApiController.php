<?php

namespace MailVotech\LeadBundle\Controller\Api;

use Doctrine\Persistence\ManagerRegistry;
use MailVotech\ApiBundle\Controller\CommonApiController;
use MailVotech\ApiBundle\Helper\EntityResultHelper;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Helper\AppVersion;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\LeadBundle\Entity\Tag;
use MailVotech\LeadBundle\Entity\TagRepository;
use MailVotech\LeadBundle\Model\TagModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;

/**
 * @extends CommonApiController<Tag>
 */
final class TagApiController extends CommonApiController
{
    public function __construct(
        CorePermissions $security,
        Translator $translator,
        EntityResultHelper $entityResultHelper,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        AppVersion $appVersion,
        RequestStack $requestStack,
        ManagerRegistry $doctrine,
        ModelFactory $modelFactory,
        EventDispatcherInterface $dispatcher,
        CoreParametersHelper $coreParametersHelper,
        TagModel $leadTagModel,
        private readonly TagRepository $tagRepository,
    ) {
        $this->model           = $leadTagModel;
        $this->entityClass     = Tag::class;
        $this->entityNameOne   = 'tag';
        $this->entityNameMulti = 'tags';

        parent::__construct($security, $translator, $entityResultHelper, $router, $formFactory, $appVersion, $requestStack, $doctrine, $modelFactory, $dispatcher, $coreParametersHelper);
    }

    /**
     * Creates new entity from provided params.
     *
     * @return object
     *
     * @throws \InvalidArgumentException
     */
    public function getNewEntity(array $params)
    {
        if (empty($params[$this->entityNameOne])) {
            throw new \InvalidArgumentException($this->translator->trans('mailvotech.lead.api.tag.required', [], 'validators'));
        }

        $tagModel = $this->model;
        \assert($tagModel instanceof TagModel);

        return $this->tagRepository->getTagByNameOrCreateNewOne($params[$this->entityNameOne]);
    }
}
