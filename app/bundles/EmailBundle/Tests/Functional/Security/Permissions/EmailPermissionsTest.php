<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\Functional\Security\Permissions;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\UserBundle\Entity\Role;
use Symfony\Component\HttpFoundation\Request;

final class EmailPermissionsTest extends MailVotechMysqlTestCase
{
    public function testEmailSendToDncPermissionIsAvailable(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/s/roles/new');
        self::assertResponseIsSuccessful();

        $this->assertStringContainsString('Send to unsubscribed contacts', (string) $this->client->getResponse()->getContent());

        $emailPermissionTab = $crawler->filter('#emailPermissionTab');
        $this->assertCount(1, $emailPermissionTab);

        $sendToDncRole = $crawler->filter('input[name="role[permissions][email:emails][]"]');
        $this->assertCount(11, $sendToDncRole);
    }

    public function testUserCanSaveSendToDncPermission(): void
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/s/roles/new');
        self::assertResponseIsSuccessful();

        $submit = $crawler->selectButton('Save & Close');
        $form   = $submit->form();
        $form['role[name]']->setValue('Send To DNC Permission');
        $form['role[isAdmin]']->setValue('0');
        $form['role[description]']->setValue('This is to send emails with "Send to DNC" permission');
        $form['role[permissions][email:emails][8]']->setValue('sendtodnc');
        $this->client->submit($form);
        self::assertResponseIsSuccessful();

        $role               = $this->em->getRepository(Role::class)->findOneBy(['name' => 'Send To DNC Permission']);
        $this->assertInstanceOf(Role::class, $role);
        $readablePermission = $role->getRawPermissions();
        $this->assertSame(['email:emails' => [8 => 'sendtodnc']], $readablePermission);
    }
}
