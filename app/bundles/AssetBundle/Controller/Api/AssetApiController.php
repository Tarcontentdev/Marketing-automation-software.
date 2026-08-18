<?php

namespace MailVotech\AssetBundle\Controller\Api;

use Doctrine\Persistence\ManagerRegistry;
use MailVotech\ApiBundle\Controller\CommonApiController;
use MailVotech\ApiBundle\Helper\EntityResultHelper;
use MailVotech\AssetBundle\Entity\Asset;
use MailVotech\AssetBundle\Model\AssetModel;
use MailVotech\CoreBundle\Factory\ModelFactory;
use MailVotech\CoreBundle\Helper\AppVersion;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\CoreBundle\Translation\Translator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @extends CommonApiController<Asset>
 */
final class AssetApiController extends CommonApiController
{
    /**
     * @var AssetModel|null
     */
    protected $model;

    public function __construct(
        CorePermissions $security,
        Translator $translator,
        EntityResultHelper $entityResultHelper,
        RouterInterface $router,
        FormFactoryInterface $formFactory,
        AppVersion $appVersion,
        RequestStack $requestStack,
        private readonly CoreParametersHelper $parametersHelper,
        ManagerRegistry $doctrine,
        ModelFactory $modelFactory,
        EventDispatcherInterface $dispatcher,
        CoreParametersHelper $coreParametersHelper,
        AssetModel $assetModel,
    ) {
        $this->model            = $assetModel;
        $this->entityClass      = Asset::class;
        $this->entityNameOne    = 'asset';
        $this->entityNameMulti  = 'assets';
        $this->serializerGroups = ['assetDetails', 'categoryList', 'publishDetails'];

        parent::__construct($security, $translator, $entityResultHelper, $router, $formFactory, $appVersion, $requestStack, $doctrine, $modelFactory, $dispatcher, $coreParametersHelper);
    }

    /**
     * Gives child controllers opportunity to analyze and do whatever to an entity before going through serializer.
     */
    protected function preSerializeEntity(object $entity, string $action = 'view'): void
    {
        // During delete responses Doctrine may already de-reference the entity ID.
        // In that case, generating a public slug is not possible and should be skipped.
        if (null === $entity->getId()) {
            return;
        }

        $entity->setDownloadUrl(
            $this->model->generateUrl($entity, true)
        );
    }

    /**
     * Convert posted parameters into what the form needs in order to successfully bind.
     *
     * @return mixed
     */
    protected function prepareParametersForBinding(Request $request, $parameters, $entity, $action)
    {
        $assetDir = $this->parametersHelper->get('upload_dir');
        $entity->setUploadDir($assetDir);

        if (isset($parameters['file'])) {
            if ('local' === $parameters['storageLocation']) {
                $entity->setPath($parameters['file']);
                $entity->setFileInfoFromFile();

                if (null === $entity->loadFile()) {
                    return $this->returnError('File '.$parameters['file'].' was not found in the asset directory.', Response::HTTP_BAD_REQUEST);
                }
            } elseif ('remote' === $parameters['storageLocation']) {
                $parameters['remotePath'] = $parameters['file'];
                $entity->setTitle($parameters['title']);
                $entity->setStorageLocation('remote');
                $entity->setRemotePath($parameters['remotePath']);
                $entity->preUpload();
                $entity->upload();
            }

            unset($parameters['file']);
        } elseif ('new' === $action) {
            return $this->returnError('File of the asset is required.', Response::HTTP_BAD_REQUEST);
        }

        return $parameters;
    }
}
