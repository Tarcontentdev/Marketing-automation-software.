<?php

namespace MailVotech\AssetBundle\EventListener;

use MailVotech\AssetBundle\Helper\TokenHelper;
use MailVotech\CoreBundle\DTO\TokenFormatOptions;
use MailVotech\CoreBundle\Event\BuilderEvent;
use MailVotech\CoreBundle\Helper\BuilderTokenHelperFactory;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotech\EmailBundle\EmailEvents;
use MailVotech\EmailBundle\Event\EmailSendEvent;
use MailVotech\LeadBundle\Tracker\ContactTracker;
use MailVotech\PageBundle\Event\PageDisplayEvent;
use MailVotech\PageBundle\PageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class BuilderSubscriber implements EventSubscriberInterface
{
    private string $assetToken = '{assetlink=(.*?)}';

    public function __construct(
        private readonly CorePermissions $security,
        private readonly TokenHelper $tokenHelper,
        private readonly ContactTracker $contactTracker,
        private readonly BuilderTokenHelperFactory $builderTokenHelperFactory,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            EmailEvents::EMAIL_ON_BUILD   => ['onBuilderBuild', 0],
            EmailEvents::EMAIL_ON_SEND    => ['onEmailGenerate', 0],
            EmailEvents::EMAIL_ON_DISPLAY => ['onEmailGenerate', 0],
            PageEvents::PAGE_ON_BUILD     => ['onBuilderBuild', 0],
            PageEvents::PAGE_ON_DISPLAY   => ['onPageDisplay', 0],
        ];
    }

    public function onBuilderBuild(BuilderEvent $event): void
    {
        if ($event->tokensRequested($this->assetToken)) {
            $tokenHelper = $this->builderTokenHelperFactory->getBuilderTokenHelper('asset');
            $tokenFilter = $event->getTokenFilter();
            $tokens      = $tokenHelper->getFormattedTokens(
                $this->assetToken,
                TokenFormatOptions::linkWithId('mailvotech.asset.asset', $this->assetToken),
                'label' === $tokenFilter['target'] ? $tokenFilter['filter'] : '',
                'title'
            );
            if ([] !== $tokens) {
                $event->addTokens($tokens);
            }
        }
    }

    public function onEmailGenerate(EmailSendEvent $event): void
    {
        $lead   = $event->getLead();
        $leadId = (int) (null !== $lead ? $lead['id'] : null);
        $email  = $event->getEmail();
        $tokens = $this->generateTokensFromContent($event, $leadId, $event->getSource(), null === $email ? null : $email->getId());
        $event->addTokens($tokens);
    }

    public function onPageDisplay(PageDisplayEvent $event): void
    {
        if (!$lead = $event->getLead()) {
            $lead = $this->security->isAnonymous() ? $this->contactTracker->getContact() : null;
        }

        $leadId  = $lead ? $lead->getId() : null;
        $page    = $event->getPage();
        $tokens  = $this->generateTokensFromContent($event, $leadId, ['page', $page->getId()]);
        $content = $event->getContent();

        if ([] !== $tokens) {
            $content = str_ireplace(array_keys($tokens), $tokens, $content);
        }
        $event->setContent($content);
    }

    /**
     * @param array    $source
     * @param int|null $emailId
     *
     * @return mixed[]
     */
    private function generateTokensFromContent(EmailSendEvent|PageDisplayEvent $event, ?int $leadId, $source = [], $emailId = null): array
    {
        if ($event instanceof PageDisplayEvent || ($event instanceof EmailSendEvent && $event->shouldAppendClickthrough())) {
            $clickthrough = [
                'source' => $source,
                'lead'   => $leadId ?? false,
                'email'  => $emailId ?? false,
            ];
        }

        return $this->tokenHelper->findAssetTokens($event->getContent(), array_filter($clickthrough ?? []));
    }
}
