<?php

namespace MailVotechPlugin\MailVotechFocusBundle\Controller;

use MailVotech\CoreBundle\Controller\CommonController;
use MailVotech\CoreBundle\Helper\TrackingPixelHelper;
use MailVotech\LeadBundle\Tracker\ContactTracker;
use MailVotechPlugin\MailVotechFocusBundle\Entity\Stat;
use MailVotechPlugin\MailVotechFocusBundle\Event\FocusViewEvent;
use MailVotechPlugin\MailVotechFocusBundle\FocusEvents;
use MailVotechPlugin\MailVotechFocusBundle\Model\FocusModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Service\Attribute\Required;

final class PublicController extends CommonController
{
    private FocusModel $focusModel;

    #[Required]
    public function autowirePublicController(
        FocusModel $focusModel,
    ): void {
        $this->focusModel = $focusModel;
    }

    public function generateAction($id): Response
    {
        // Don't store a visitor with this request
        defined('MAILVOTECH_NON_TRACKABLE_REQUEST') || define('MAILVOTECH_NON_TRACKABLE_REQUEST', 1);
        $focus = $this->focusModel->getEntity($id);

        if ($focus) {
            if (!$focus->isPublished()) {
                return new Response('', Response::HTTP_NOT_FOUND);
            }

            $content = $this->focusModel->generateJavascript($focus);

            return new Response($content, 200, ['Content-Type' => 'application/javascript']);
        }

        return new Response('', Response::HTTP_NOT_FOUND);
    }

    public function viewPixelAction(Request $request, ContactTracker $contactTracker): Response
    {
        $id = $request->get('id', false);
        if ($id) {
            $focus = $this->focusModel->getEntity($id);

            $lead = $contactTracker->getContact();

            if ($focus && $focus->isPublished() && $lead) {
                $stat = $this->focusModel->addStat($focus, Stat::TYPE_NOTIFICATION, $request, $lead);
                if ($stat && $this->dispatcher->hasListeners(FocusEvents::FOCUS_ON_VIEW)) {
                    $event = new FocusViewEvent($stat);
                    $this->dispatcher->dispatch($event, FocusEvents::FOCUS_ON_VIEW);
                    unset($event);
                }
            }
        }

        return TrackingPixelHelper::getResponse($request);
    }
}
