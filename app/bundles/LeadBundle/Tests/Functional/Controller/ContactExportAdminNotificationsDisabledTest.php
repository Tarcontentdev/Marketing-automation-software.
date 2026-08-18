<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Functional\Controller;

use MailVotech\CoreBundle\Entity\AuditLog;
use MailVotech\CoreBundle\Entity\Notification;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\EmailBundle\Mailer\Message\MailVotechMessage;
use MailVotech\LeadBundle\Command\ContactScheduledExportCommand;
use MailVotech\LeadBundle\Entity\ContactExportScheduler;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\UserBundle\Entity\Role;
use MailVotech\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class ContactExportAdminNotificationsDisabledTest extends MailVotechMysqlTestCase
{
    /**
     * @var array<string>
     */
    private array $filePaths = [];

    protected function setUp(): void
    {
        $this->configParams['contact_export_dir']           = '/tmp';
        $this->configParams['contact_export_notify_admins'] = false;

        parent::setUp();
    }

    protected function beforeTearDown(): void
    {
        foreach ($this->filePaths as $filePath) {
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }

    public function testAdminNotificationsAreDisabledButRequesterEmailAndAuditLogsRemain(): void
    {
        $this->createContacts();
        $this->setAdminUser();

        $adminRole      = $this->createRole(true, 'Security Admin');
        $secondaryAdmin = $this->createUser($adminRole, 'security-admin', 'security.admin@email.com');
        $secondaryAdmin->setFirstName('Security');
        $secondaryAdmin->setLastName('Admin');

        $this->em->flush();

        $this->client->request(
            Request::METHOD_POST,
            's/contacts/batchExport',
            ['filetype' => 'csv']
        );
        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var ContactExportScheduler $contactExportScheduler */
        $contactExportScheduler   = $this->checkContactExportScheduler(1)[0];
        $contactExportSchedulerId = $contactExportScheduler->getId();
        $this->assertNotNull($contactExportSchedulerId);
        $requestingAdmin        = $this->em->getRepository(User::class)->findOneBy(['username' => 'admin']);

        $requesterNotifications = $this->em->getRepository(Notification::class)->findBy(
            [
                'user'   => $requestingAdmin,
                'header' => 'mailvotech.lead.export.being.prepared.header',
            ]
        );
        $this->assertCount(1, $requesterNotifications);

        $adminNotifications = $this->em->getRepository(Notification::class)->findBy(
            [
                'user'   => $secondaryAdmin,
                'header' => 'mailvotech.lead.export.admin.notification.header',
            ]
        );
        $this->assertCount(0, $adminNotifications);

        /** @var AuditLog|null $createAuditLog */
        $createAuditLog = $this->em->getRepository(AuditLog::class)->findOneBy(
            [
                'object'   => 'ContactExportScheduler',
                'objectId' => $contactExportSchedulerId,
                'action'   => 'create',
            ]
        );
        $this->assertInstanceOf(AuditLog::class, $createAuditLog);

        $this->testSymfonyCommand(ContactScheduledExportCommand::COMMAND_NAME, ['--ids' => $contactExportScheduler->getId()]);
        $this->checkContactExportScheduler(0);

        /** @var AuditLog|null $sendEmailAuditLog */
        $sendEmailAuditLog = $this->em->getRepository(AuditLog::class)->findOneBy(
            [
                'object'   => 'ContactExportScheduler',
                'objectId' => $contactExportSchedulerId,
                'action'   => 'sendEmail',
            ]
        );
        $this->assertInstanceOf(AuditLog::class, $sendEmailAuditLog);

        /** @var CoreParametersHelper $coreParametersHelper */
        $coreParametersHelper = self::getContainer()->get(CoreParametersHelper::class);
        $zipFileName          = 'contacts_export_'.$contactExportScheduler->getScheduledDateTime()->format('Y_m_d_H_i_s').'.zip';
        $this->filePaths[]    = $filePath = $coreParametersHelper->get('contact_export_dir').'/'.$zipFileName;
        $downloadLink         = $this->router->generate(
            'mailvotech_contact_export_download',
            ['fileName' => basename($filePath)],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $messages = self::getMailerMessages();
        $this->assertCount(1, $messages);
        $this->assertInstanceOf(User::class, $requestingAdmin);

        $requesterEmail = $this->findMailerMessageByRecipient($requestingAdmin->getEmail());
        $this->assertInstanceOf(MailVotechMessage::class, $requesterEmail);
        $this->assertSame('Your contact export is ready', $requesterEmail->getSubject());
        $this->assertStringContainsString($downloadLink, (string) $requesterEmail->getHtmlBody());
        $this->assertStringContainsString($zipFileName, (string) $requesterEmail->getHtmlBody());

        $this->assertNotInstanceOf(MailVotechMessage::class, $this->findMailerMessageByRecipient($secondaryAdmin->getEmail()));
    }

    private function createContacts(): void
    {
        $contacts = [];

        for ($i = 1; $i <= 2; ++$i) {
            $contact = new Lead();
            $contact
                ->setFirstname('ContactFirst'.$i)
                ->setLastname('ContactLast'.$i)
                ->setEmail('DisabledFirstLast'.$i.'@email.com');
            $contacts[] = $contact;
        }

        $leadModel = self::getContainer()->get(LeadModel::class);
        $leadModel->saveEntities($contacts);
    }

    /**
     * @return array<mixed>
     */
    private function checkContactExportScheduler(int $count): array
    {
        $repo    = $this->em->getRepository(ContactExportScheduler::class);
        $allRows = $repo->findAll();
        $this->assertCount($count, $allRows);

        return $allRows;
    }

    private function setAdminUser(): void
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['username' => 'admin']);
        $this->assertInstanceOf(User::class, $user);
        $this->loginUser($user);
        $this->client->setServerParameter('PHP_AUTH_USER', 'admin');
        $this->client->setServerParameter('PHP_AUTH_PW', 'Maut1cR0cks!');
    }

    private function createRole(bool $isAdmin = false, string $name = 'Role'): Role
    {
        $role = new Role();
        $role->setName($name);
        $role->setIsAdmin($isAdmin);

        $this->em->persist($role);

        return $role;
    }

    private function createUser(Role $role, string $username, string $email): User
    {
        $user = new User();
        $user->setFirstName('First');
        $user->setLastName('Last');
        $user->setUsername($username);
        $user->setEmail($email);
        $hasher = self::getContainer()->get(PasswordHasherFactoryInterface::class)->getPasswordHasher($user);
        $this->assertInstanceOf(PasswordHasherInterface::class, $hasher);
        $user->setPassword($hasher->hash('Maut1cR0cks!'));
        $user->setRole($role);
        $user->setIsPublished(true);

        $this->em->persist($user);

        return $user;
    }

    private function findMailerMessageByRecipient(string $email): ?MailVotechMessage
    {
        foreach (self::getMailerMessages() as $message) {
            if (!$message instanceof MailVotechMessage) {
                continue;
            }

            foreach ($message->getTo() as $recipient) {
                if ($recipient->getAddress() === $email) {
                    return $message;
                }
            }
        }

        return null;
    }
}
