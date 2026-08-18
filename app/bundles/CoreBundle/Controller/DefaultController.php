<?php

namespace MailVotech\CoreBundle\Controller;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\Event\GlobalSearchEvent;
use MailVotech\CoreBundle\Model\NotificationModel;
use MailVotech\PageBundle\Model\PageModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * Almost all other MailVotech Bundle controllers extend this default controller.
 */
final class DefaultController extends CommonController
{
    private NotificationModel $notificationModel;

    private PageModel $pageModel;

    #[Required]
    public function autowireDefaultController(
        NotificationModel $notificationModel,
        PageModel $pageModel,
    ): void {
        $this->notificationModel = $notificationModel;
        $this->pageModel = $pageModel;
    }

    public function indexAction(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse|Response
    {
        $root = $this->coreParametersHelper->get('webroot');

        if (empty($root)) {
            return $this->redirectToRoute('mailvotech_dashboard_index');
        }
        $page      = $this->pageModel->getEntity($root);

        if (!$page instanceof \MailVotech\PageBundle\Entity\Page) {
            return $this->notFound();
        }

        $slug = $this->pageModel->generateSlug($page);

        $request->attributes->set('ignore_mismatch', true);

        return $this->forward('MailVotech\PageBundle\Controller\PublicController::indexAction', ['slug' => $slug]);
    }

    public function globalSearchAction(Request $request): Response
    {
        $searchStr = $request->get('global_search', $request->getSession()->get('mailvotech.global_search', ''));
        $request->getSession()->set('mailvotech.global_search', $searchStr);

        if (!empty($searchStr)) {
            $event = new GlobalSearchEvent($searchStr, $this->translator);
            $this->dispatcher->dispatch($event, CoreEvents::GLOBAL_SEARCH);
            $results = $event->getResults();
        } else {
            $results = [];
        }

        return $this->render('@MailVotechCore/GlobalSearch/globalsearch.html.twig',
            [
                'results'      => $results,
                'searchString' => $searchStr,
            ]
        );
    }

    public function notificationsAction(): Response
    {
        [$notifications, $showNewIndicator, $updateMessage] = $this->notificationModel->getNotificationContent(null, false, 200);

        return $this->delegateView(
            [
                'contentTemplate' => '@MailVotechCore/Notification/notifications.html.twig',
                'viewParameters'  => [
                    'showNewIndicator' => $showNewIndicator,
                    'notifications'    => $notifications,
                    'updateMessage'    => $updateMessage,
                ],
            ]
        );
    }
}
