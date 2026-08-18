<?php

declare(strict_types=1);

namespace MailVotech\AssetBundle\EventListener;

use MailVotech\AssetBundle\AssetEvents;
use MailVotech\AssetBundle\Event\AssetLoadEvent;
use MailVotech\AssetBundle\Form\Type\PointActionAssetDownloadType;
use MailVotech\AssetBundle\Helper\PointActionHelper;
use MailVotech\PointBundle\Event\PointBuilderEvent;
use MailVotech\PointBundle\Model\PointModel;
use MailVotech\PointBundle\PointEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class PointSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private PointModel $pointModel,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PointEvents::POINT_ON_BUILD => ['onPointBuild', 0],
            AssetEvents::ASSET_ON_LOAD  => ['onAssetDownload', 0],
        ];
    }

    public function onPointBuild(PointBuilderEvent $event): void
    {
        $action = [
            'group'       => 'mailvotech.asset.actions',
            'label'       => 'mailvotech.asset.point.action.download',
            'description' => 'mailvotech.asset.point.action.download_descr',
            'callback'    => [PointActionHelper::class, 'validateAssetDownload'],
            'formType'    => PointActionAssetDownloadType::class,
        ];

        $event->addAction('asset.download', $action);
    }

    /**
     * Trigger point actions for asset download.
     */
    public function onAssetDownload(AssetLoadEvent $event): void
    {
        $asset = $event->getRecord()->getAsset();

        if (null !== $asset) {
            $this->pointModel->triggerAction('asset.download', $asset);
        }
    }
}
