<?php

declare(strict_types=1);

namespace MailVotech\MessengerBundle\MessageHandler;

use MailVotech\LeadBundle\Entity\LeadRepository;
use MailVotech\MessengerBundle\Exceptions\InvalidPayloadException;
use MailVotech\MessengerBundle\Message\PageHitNotification;
use MailVotech\PageBundle\Entity\Hit;
use MailVotech\PageBundle\Entity\HitRepository;
use MailVotech\PageBundle\Entity\PageRepository;
use MailVotech\PageBundle\Entity\RedirectRepository;
use MailVotech\PageBundle\Model\PageModel;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Handler\Acknowledger;

#[AsMessageHandler]
final readonly class PageHitNotificationHandler
{
    public function __construct(
        private PageRepository $pageRepository,
        private HitRepository $hitRepository,
        private LeadRepository $leadRepository,
        private LoggerInterface $logger,
        private RedirectRepository $redirectRepository,
        private PageModel $pageModel,
    ) {
    }

    /**
     * @throws InvalidPayloadException
     */
    public function __invoke(PageHitNotification $message, ?Acknowledger $ack = null): void
    {
        $parsed = $this->parseMessage($message);
        $this->pageModel->processPageHit(...$parsed);
        $this->logger->info('processed page hit #'.$message->getHitId());
    }

    /**
     * @return array<string, mixed>
     *
     * @throws InvalidPayloadException
     */
    private function parseMessage(PageHitNotification $message): array
    {
        $hit = $message->getHitId() > 0 ? $this->hitRepository->find($message->getHitId()) : null;

        $pageObject = null;
        if (null !== $message->getPageId()) {
            try {
                $pageObject = $message->isRedirect()
                    ? $this->redirectRepository->find($message->getPageId())
                    : $this->pageRepository->find($message->getPageId());
            } catch (\Exception $exception) {
                $this->logger->error(
                    sprintf('Invalid page/redirect, exception. #%s', $message->getPageId()),
                    ['message' => $message]
                );
                throw $exception;
            }

            if (null === $pageObject) {
                $this->logger->error(
                    sprintf('Invalid page/redirect, id not found. #%s', $message->getPageId())
                );
                throw new InvalidPayloadException('Missing required information', ['message' => $message]);
            }
        }

        if (!$hit instanceof Hit && $message->getHitId() > 0) {
            $this->logger->warning('Invalid hit id #'.$message->getHitId(), ['message' => $message]);

            throw new InvalidPayloadException('Invalid hit id #'.$message->getHitId(), (array) $message);
        }

        //  Lead IS mandatory field
        if (null === $lead = $this->leadRepository->find($message->getLeadId())) {
            $this->logger->error('Invalid lead id #'.$message->getLeadId(), ['message' => $message]);

            throw new InvalidPayloadException('Invalid lead id', (array) $message);
        }

        return [
            'hit'                    => $hit,
            'page'                   => $pageObject,
            'request'                => $message->getRequest(),
            'lead'                   => $lead,
            'trackingNewlyGenerated' => $message->isNew(),
            'activeRequest'          => false,
            'hitDate'                => $message->getEventTime(),
        ];
    }
}
