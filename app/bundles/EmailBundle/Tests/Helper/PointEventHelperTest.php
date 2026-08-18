<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\Helper;

use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Helper\PointEventHelper;
use MailVotech\EmailBundle\Model\EmailModel;
use MailVotech\LeadBundle\Entity\Lead;
use PHPUnit\Framework\MockObject\MockObject;

final class PointEventHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testSendEmail(): void
    {
        $lead   = new Lead();
        $lead->setFields([
            'core' => [
                'email' => [
                    'value' => 'test@test.com',
                ],
            ],
        ]);
        $event = [
            'id'         => 1,
            'properties' => [
                'email' => 1,
            ],
        ];

        $emailModel = $this->getMockEmail(true, true);
        $helper     = new PointEventHelper($emailModel);
        $result     = $helper->sendEmail($event, $lead);
        $this->assertEquals(true, $result);

        $emailModel = $this->getMockEmail(false, true);
        $helper     = new PointEventHelper($emailModel);
        $result     = $helper->sendEmail($event, $lead);
        $this->assertEquals(false, $result);

        $emailModel = $this->getMockEmail(true, false);
        $helper     = new PointEventHelper($emailModel);
        $result     = $helper->sendEmail($event, $lead);
        $this->assertEquals(false, $result);

        $emailModel = $this->getMockEmail(true, false);
        $helper     = new PointEventHelper($emailModel);
        $result     = $helper->sendEmail($event, new Lead());
        $this->assertEquals(false, $result);
    }

    private function getMockEmail(bool $published = true, bool $success = true): EmailModel&MockObject
    {
        $sendEmail = $success ? true : ['error' => 1];

        $mock = $this->getMockBuilder(EmailModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEntity', 'sendEmail'])
            ->getMock();

        $mock
            ->method('getEntity')
            ->willReturnCallback(function ($id) use ($published): Email {
                $email = new Email();
                $email->setIsPublished($published);

                return $email;
            });

        $mock
            ->method('sendEmail')
            ->willReturn($sendEmail);

        return $mock;
    }
}
