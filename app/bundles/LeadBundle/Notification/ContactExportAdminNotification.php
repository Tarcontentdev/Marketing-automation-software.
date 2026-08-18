<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Notification;

use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Model\NotificationModel;
use MailVotech\CoreBundle\Twig\Helper\DateHelper;
use MailVotech\EmailBundle\Helper\MailHelper;
use MailVotech\LeadBundle\Entity\ContactExportScheduler;
use MailVotech\UserBundle\Entity\User;
use MailVotech\UserBundle\Entity\UserRepository;
use Symfony\Contracts\Translation\TranslatorInterface;

class ContactExportAdminNotification
{
    public function __construct(
        private readonly NotificationModel $notificationModel,
        private readonly TranslatorInterface $translator,
        private readonly UserRepository $userRepository,
        private readonly MailHelper $mailHelper,
        private readonly CoreParametersHelper $coreParametersHelper,
        private readonly DateHelper $dateHelper,
    ) {
    }

    public function notifyRequested(ContactExportScheduler $contactExportScheduler): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        /** @var User $requestingUser */
        $requestingUser = $contactExportScheduler->getUser();
        $requestedAt    = $this->getScheduledDateTime($contactExportScheduler);

        foreach ($this->getAdminUsersToNotify($requestingUser) as $adminUser) {
            $this->notificationModel->addNotification(
                $this->translator->trans(
                    'mailvotech.lead.export.admin.notification',
                    [
                        '%requesting_user_name%'  => $requestingUser->getName(),
                        '%requesting_user_email%' => $requestingUser->getEmail(),
                        '%requested_at%'          => $this->formatForDisplay($requestedAt),
                        '%file_type%'             => strtoupper((string) ($contactExportScheduler->getData()['fileType'] ?? '')),
                    ]
                ),
                'info',
                false,
                'mailvotech.lead.export.admin.notification.header',
                null,
                \DateTime::createFromImmutable($requestedAt),
                $adminUser
            );
        }
    }

    public function notifyCompleted(ContactExportScheduler $contactExportScheduler): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        /** @var User $requestingUser */
        $requestingUser = $contactExportScheduler->getUser();
        $adminUsers     = $this->getAdminUsersToNotify($requestingUser);

        if ([] === $adminUsers) {
            return;
        }

        $requestedAt = $this->getScheduledDateTime($contactExportScheduler);
        $completedAt = new \DateTimeImmutable();
        $fileType    = strtoupper((string) ($contactExportScheduler->getData()['fileType'] ?? ''));
        $message     = $this->translator->trans(
            'mailvotech.lead.export.admin.email',
            [
                '%requesting_user_name%'  => $requestingUser->getName(),
                '%requesting_user_email%' => $requestingUser->getEmail(),
                '%requested_at%'          => $this->formatForDisplay($requestedAt),
                '%completed_at%'          => $this->formatForDisplay($completedAt),
                '%status%'                => $this->translator->trans('mailvotech.lead.export.status.completed'),
                '%file_type%'             => $fileType,
            ]
        );

        $primaryAdmin = array_shift($adminUsers);

        $mailer = $this->mailHelper->getMailer(true);
        $mailer->setTo([$primaryAdmin->getEmail() => $primaryAdmin->getName()]);

        if ([] !== $adminUsers) {
            $mailer->setCc(
                array_reduce(
                    $adminUsers,
                    static function (array $recipients, User $adminUser): array {
                        $recipients[$adminUser->getEmail()] = $adminUser->getName();

                        return $recipients;
                    },
                    []
                )
            );
        }

        $mailer->setSubject($this->translator->trans('mailvotech.lead.export.admin.email_subject'));
        $mailer->setBody($message);
        $mailer->parsePlainText($message);
        $mailer->send(true);
    }

    /**
     * @return User[]
     */
    private function getAdminUsersToNotify(User $requestingUser): array
    {
        return array_values(
            array_filter(
                $this->userRepository->getAllAdminUsers(),
                static fn (User $adminUser): bool => $adminUser->isPublished()
                    && $adminUser->getId() !== $requestingUser->getId()
            )
        );
    }

    private function isEnabled(): bool
    {
        return (bool) $this->coreParametersHelper->get('contact_export_notify_admins');
    }

    private function getScheduledDateTime(ContactExportScheduler $contactExportScheduler): \DateTimeImmutable
    {
        $scheduledDateTime = $contactExportScheduler->getScheduledDateTime();
        \assert($scheduledDateTime instanceof \DateTimeImmutable);

        return $scheduledDateTime;
    }

    private function formatForDisplay(\DateTimeImmutable $dateTime): string
    {
        return $this->dateHelper->toFull(\DateTime::createFromInterface($dateTime));
    }
}
