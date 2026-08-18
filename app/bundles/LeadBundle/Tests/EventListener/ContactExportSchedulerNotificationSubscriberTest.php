<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\EventListener;

use MailVotech\CoreBundle\Model\NotificationModel;
use MailVotech\LeadBundle\Entity\ContactExportScheduler;
use MailVotech\LeadBundle\Event\ContactExportSchedulerEvent;
use MailVotech\LeadBundle\EventListener\ContactExportSchedulerNotificationSubscriber;
use MailVotech\LeadBundle\Notification\ContactExportAdminNotification;
use MailVotech\UserBundle\Entity\Role;
use MailVotech\UserBundle\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactExportSchedulerNotificationSubscriberTest extends TestCase
{
    public function testContactExportScheduledNotifiesRequesterAndDelegatesAdminNotifications(): void
    {
        $requestingUser = $this->createUser(10, 'Requester User', 'requester@example.com');

        $contactExportScheduler = (new ContactExportScheduler())
            ->setUser($requestingUser)
            ->setScheduledDateTime(new \DateTimeImmutable('2026-05-12 10:30:00 +00:00'))
            ->setData(['fileType' => 'csv']);

        $notificationModel = new class() extends NotificationModel {
            /**
             * @var array<int, array<int, mixed>>
             */
            public array $notifications = [];

            public function __construct()
            {
                // Intentionally bypass the parent constructor because this test double only records notifications.
            }

            public function addNotification($message, $type = null, $isRead = false, $header = null, $iconClass = null, ?\DateTime $datetime = null, ?User $user = null, ?string $deduplicateValue = null, ?\DateTime $deduplicateDateTimeFrom = null): void
            {
                $this->notifications[] = func_get_args();
            }
        };

        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')
            ->with('mailvotech.lead.export.being.prepared', ['%user_email%' => 'requester@example.com'])
            ->willReturn('Requester notification for requester@example.com');

        $contactExportAdminNotification = $this->createMock(ContactExportAdminNotification::class);
        $contactExportAdminNotification->expects($this->once())
            ->method('notifyRequested')
            ->with($contactExportScheduler);

        $subscriber = new ContactExportSchedulerNotificationSubscriber($notificationModel, $translator, $contactExportAdminNotification);
        $subscriber->onContactExportScheduled(new ContactExportSchedulerEvent($contactExportScheduler));

        $this->assertCount(1, $notificationModel->notifications);
        $this->assertSame('Requester notification for requester@example.com', $notificationModel->notifications[0][0]);
        $this->assertSame('info', $notificationModel->notifications[0][1]);
        $this->assertFalse($notificationModel->notifications[0][2]);
        $this->assertSame('mailvotech.lead.export.being.prepared.header', $notificationModel->notifications[0][3]);
        $this->assertSame($requestingUser, $notificationModel->notifications[0][6]);
    }

    public function testContactExportCompletedDelegatesAdminNotifications(): void
    {
        $requestingUser = $this->createUser(10, 'Requester User', 'requester@example.com');

        $contactExportScheduler = (new ContactExportScheduler())
            ->setUser($requestingUser)
            ->setScheduledDateTime(new \DateTimeImmutable('2026-05-12 10:30:00 +00:00'))
            ->setData(['fileType' => 'csv']);

        $notificationModel = $this->createStub(NotificationModel::class);
        $translator        = $this->createStub(TranslatorInterface::class);

        $contactExportAdminNotification = $this->createMock(ContactExportAdminNotification::class);
        $contactExportAdminNotification->expects($this->once())
            ->method('notifyCompleted')
            ->with($contactExportScheduler);

        $subscriber = new ContactExportSchedulerNotificationSubscriber($notificationModel, $translator, $contactExportAdminNotification);
        $subscriber->onContactExportEmailSent(new ContactExportSchedulerEvent($contactExportScheduler));
    }

    private function createUser(int $id, string $name, string $email): User
    {
        $role = new Role();
        $role->setName('User');

        // Use a tiny test double so we can assign an entity ID without reflection-based private property access.
        $user = new class() extends User {
            public function setId(int $id): void
            {
                $this->id = $id;
            }
        };
        $user->setFirstName(explode(' ', $name)[0]);
        $user->setLastName(explode(' ', $name)[1] ?? 'User');
        $user->setEmail($email);
        $user->setUsername($email);
        $user->setRole($role);
        $user->setId($id);

        return $user;
    }
}
