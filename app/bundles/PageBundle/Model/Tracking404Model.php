<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\Model;

use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\LeadBundle\Tracker\ContactTracker;
use MailVotech\PageBundle\Entity\Page;
use MailVotech\PageBundle\Entity\Redirect;
use Symfony\Component\HttpFoundation\Request;

class Tracking404Model
{
    public function __construct(
        private readonly CoreParametersHelper $coreParametersHelper,
        private readonly ContactTracker $contactTracker,
        private readonly PageModel $pageModel,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function hitPage(Redirect|Page|null $entity, Request $request): void
    {
        $this->pageModel->hitPage($entity, $request, 404);
    }

    public function isTrackable(): bool
    {
        if (!$this->coreParametersHelper->get('do_not_track_404_anonymous')) {
            return true;
        }
        // already tracked and identified contact
        if ($lead = $this->contactTracker->getContactByTrackedDevice()) {
            if (!$lead->isAnonymous()) {
                return true;
            }
        }

        return false;
    }
}
