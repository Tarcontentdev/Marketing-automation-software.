<?php

namespace MailVotech\AssetBundle\EventListener;

use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use MailVotech\AssetBundle\Entity\Asset;
use MailVotech\AssetBundle\Entity\AssetRepository;
use MailVotech\AssetBundle\Form\Type\FormSubmitActionDownloadFileType;
use MailVotech\AssetBundle\Model\AssetModel;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\ThemeHelperInterface;
use MailVotech\CoreBundle\Twig\Helper\AnalyticsHelper;
use MailVotech\CoreBundle\Twig\Helper\AssetsHelper;
use MailVotech\FormBundle\Entity\Form;
use MailVotech\FormBundle\Event\FormBuilderEvent;
use MailVotech\FormBundle\Event\SubmissionEvent;
use MailVotech\FormBundle\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

final readonly class FormSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AssetModel $assetModel,
        private TranslatorInterface $translator,
        private AnalyticsHelper $analyticsHelper,
        private AssetsHelper $assetsHelper,
        private ThemeHelperInterface $themeHelper,
        private CoreParametersHelper $coreParametersHelper,
        private AssetRepository $assetRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::FORM_ON_BUILD                 => ['onFormBuilder', 0],
            FormEvents::ON_EXECUTE_SUBMIT_ACTION      => [
                ['onFormSubmitActionAssetDownload', 0],
                ['onFormSubmitActionDownloadFile', 0],
            ],
        ];
    }

    /**
     * Add a lead generation action to available form submit actions.
     */
    public function onFormBuilder(FormBuilderEvent $event): void
    {
        $event->addSubmitAction('asset.download', [
            'group'              => 'mailvotech.asset.actions',
            'label'              => 'mailvotech.asset.asset.submitaction.downloadfile',
            'description'        => 'mailvotech.asset.asset.submitaction.downloadfile_descr',
            'formType'           => FormSubmitActionDownloadFileType::class,
            'formTypeCleanMasks' => ['message' => 'html'],
            'eventName'          => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
            'template'           => '@MailVotechAsset/Action/asset.html.twig',
        ]);
    }

    public function onFormSubmitActionAssetDownload(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('asset.download')) {
            return;
        }

        $properties = $event->getAction()->getProperties();
        $assetId    = $properties['asset'];
        $categoryId = $properties['category'] ?? null;
        $asset      = null;

        if (null !== $assetId) {
            $asset = $this->assetModel->getEntity($assetId);
        } elseif (null !== $categoryId) {
            try {
                $asset = $this->assetRepository->getLatestAssetForCategory($categoryId);
            } catch (NoResultException|NonUniqueResultException) {
                $asset = null;
            }
        }

        if ($asset instanceof Asset && $asset->isPublished()) {
            $event->setPostSubmitCallback('asset.download_file', [
                'eventName' => FormEvents::ON_EXECUTE_SUBMIT_ACTION,
                'form'      => $event->getAction()->getForm(),
                'asset'     => $asset,
                'message'   => $properties['message'] ?? '',
            ]);
        }
    }

    public function onFormSubmitActionDownloadFile(SubmissionEvent $event): void
    {
        if (false === $event->checkContext('asset.download_file')) {
            return;
        }

        /*
         * No further actions can run after this, as we need to send the
         * download response to the client.
         */
        $event->stopPropagation();

        /**
         * @var Form   $form
         * @var Asset  $asset
         * @var string $message
         * @var bool   $messengerMode
         */
        [
            'form'          => $form,
            'asset'         => $asset,
            'message'       => $message,
            'messengerMode' => $messengerMode,
        ]    = $event->getPostSubmitCallback('asset.download_file');

        $url = $this->assetModel->generateUrl($asset, true, [
            'lead'    => $event->getLead() ? $event->getLead()->getId() : null,
            'channel' => ['form' => $form->getId()],
        ]).'&stream=0';

        if ($messengerMode) {
            $event->setPostSubmitResponse(['download' => $url]);

            return;
        }

        $msg = $message.$this->translator->trans('mailvotech.asset.asset.submitaction.downloadfile.msg', [
            '%url%' => $url,
        ]);

        $analytics = $this->analyticsHelper->getCode();

        if (!empty($analytics)) {
            $this->assetsHelper->addCustomDeclaration($analytics);
        }

        $event->setPostSubmitResponse(new Response(
            $this->themeHelper->renderThemeTemplate(
                $this->themeHelper->checkForTwigTemplate('@themes/'.$this->coreParametersHelper->get('theme').'/html/message.html.twig'),
                [
                    'message'  => $msg,
                    'type'     => 'notice',
                    'template' => $this->coreParametersHelper->get('theme'),
                ]
            )
        ));
    }
}
