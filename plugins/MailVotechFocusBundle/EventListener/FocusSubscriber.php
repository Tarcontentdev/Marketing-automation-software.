<?php

namespace MailVotechPlugin\MailVotechFocusBundle\EventListener;

use MailVotech\AssetBundle\Helper\TokenHelper as AssetTokenHelper;
use MailVotech\CoreBundle\Event as MailVotechEvents;
use MailVotech\CoreBundle\Helper\InputHelper;
use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\CoreBundle\Model\AuditLogModel;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Helper\TokenHelper;
use MailVotech\PageBundle\Entity\Trackable;
use MailVotech\PageBundle\Helper\TokenHelper as PageTokenHelper;
use MailVotech\PageBundle\Model\TrackableModel;
use MailVotech\ReportBundle\Event\ReportBuilderEvent;
use MailVotech\ReportBundle\ReportEvents;
use MailVotechPlugin\MailVotechFocusBundle\Event\FocusEvent;
use MailVotechPlugin\MailVotechFocusBundle\FocusEvents;
use MailVotechPlugin\MailVotechFocusBundle\Model\FocusModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

final readonly class FocusSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RouterInterface $router,
        private IpLookupHelper $ipHelper,
        private AuditLogModel $auditLogModel,
        private TrackableModel $trackableModel,
        private PageTokenHelper $pageTokenHelper,
        private AssetTokenHelper $assetTokenHelper,
        private FocusModel $focusModel,
        private RequestStack $requestStack,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST          => ['onKernelRequest', 0],
            FocusEvents::POST_SAVE         => ['onFocusPostSave', 0],
            FocusEvents::POST_DELETE       => ['onFocusDelete', 0],
            FocusEvents::TOKEN_REPLACEMENT => ['onTokenReplacement', 0],
            ReportEvents::REPORT_ON_BUILD  => ['onReportBuild', -10],
        ];
    }

    /**
     * Check and hijack the form's generate link if the ID has mf- in it.
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            // get the current event request
            $request    = $event->getRequest();
            $requestUri = $request->getRequestUri();

            $formGenerateUrl = $this->router->generate('mailvotech_form_generateform');

            if (str_contains($requestUri, $formGenerateUrl)) {
                $id = InputHelper::_($this->requestStack->getCurrentRequest()->get('id'));
                if (str_starts_with($id, 'mf-')) {
                    $mfId             = str_replace('mf-', '', $id);
                    $focusGenerateUrl = $this->router->generate('mailvotech_focus_generate', ['id' => $mfId]);

                    $event->setResponse(new RedirectResponse($focusGenerateUrl));
                }
            }
        }
    }

    /**
     * Add an entry to the audit log.
     */
    public function onFocusPostSave(FocusEvent $event): void
    {
        $entity = $event->getFocus();
        if ($details = $event->getChanges()) {
            $log = [
                'bundle'    => 'focus',
                'object'    => 'focus',
                'objectId'  => $entity->getId(),
                'action'    => ($event->isNew()) ? 'create' : 'update',
                'details'   => $details,
                'ipAddress' => $this->ipHelper->getIpAddressFromRequest(),
            ];
            $this->auditLogModel->writeToLog($log);
        }
    }

    /**
     * Add a delete entry to the audit log.
     */
    public function onFocusDelete(FocusEvent $event): void
    {
        $entity = $event->getFocus();
        $log    = [
            'bundle'    => 'focus',
            'object'    => 'focus',
            'objectId'  => $entity->deletedId,
            'action'    => 'delete',
            'details'   => ['name' => $entity->getName()],
            'ipAddress' => $this->ipHelper->getIpAddressFromRequest(),
        ];
        $this->auditLogModel->writeToLog($log);
    }

    public function onReportBuild(ReportBuilderEvent $event): void
    {
        $tables = $event->getTables();

        if (!isset($tables['audit.log']['columns']['al.bundle']['list'])) {
            return;
        }

        $tables['audit.log']['columns']['al.object']['list']['focus'] = 'focus';

        $event->addTable('audit.log', $tables['audit.log']);
    }

    public function onTokenReplacement(MailVotechEvents\TokenReplacementEvent $event): void
    {
        /** @var Lead $lead */
        $lead         = $event->getLead();
        $content      = $event->getContent();
        $clickthrough = $event->getClickthrough();

        if ($content) {
            $tokens = array_merge(
                $this->pageTokenHelper->findPageTokens($content, $clickthrough),
                $this->assetTokenHelper->findAssetTokens($content, $clickthrough)
            );

            if ($lead && $lead->getId()) {
                $tokens = array_merge($tokens, TokenHelper::findLeadTokens($content, $lead->getProfileFields()));
            }

            [$content, $trackables] = $this->trackableModel->parseContentForTrackables(
                $content,
                $tokens,
                'focus',
                $clickthrough['focus_id']
            );

            $focus = $this->focusModel->getEntity($clickthrough['focus_id']);

            /**
             * @var string    $token
             * @var Trackable $trackable
             */
            foreach ($trackables as $token => $trackable) {
                $tokens[$token] = $this->trackableModel->generateTrackableUrl($trackable, $clickthrough, false, $focus->getUtmTags());
            }

            $tokens = array_merge($tokens, $event->getTokens());

            $content = str_replace(array_keys($tokens), array_values($tokens), $content);

            $event->setContent($content);
        }
    }
}
