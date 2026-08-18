<?php

namespace MailVotech\NotificationBundle\Controller;

use MailVotech\CoreBundle\Controller\CommonController;
use MailVotech\CoreBundle\Twig\Helper\AssetsHelper;
use MailVotech\PageBundle\Entity\Page;
use MailVotech\PageBundle\Event\PageDisplayEvent;
use MailVotech\PageBundle\PageEvents;
use Symfony\Component\HttpFoundation\Response;

final class PopupController extends CommonController
{
    public function indexAction(AssetsHelper $assetsHelper): Response
    {
        $assetsHelper->addStylesheet('/app/bundles/NotificationBundle/Assets/css/popup/popup.css');

        $response = $this->render(
            '@MailVotechNotification/Popup/index.html.twig',
            [
                'siteUrl' => $this->coreParametersHelper->get('site_url'),
            ]
        );

        $content = $response->getContent();

        $event = new PageDisplayEvent($content, new Page());
        $this->dispatcher->dispatch($event, PageEvents::PAGE_ON_DISPLAY);
        $content = $event->getContent();

        return $response->setContent($content);
    }
}
