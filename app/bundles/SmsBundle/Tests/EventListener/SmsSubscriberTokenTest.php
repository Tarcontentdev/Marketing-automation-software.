<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\Tests\EventListener;

use MailVotech\AssetBundle\Entity\Asset;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Model\LeadModel;
use MailVotech\PageBundle\Entity\Page;
use MailVotech\SmsBundle\Entity\Sms;
use MailVotech\SmsBundle\Model\SmsModel;
use MailVotech\SmsBundle\Tests\SmsTestHelperTrait;

final class SmsSubscriberTokenTest extends MailVotechMysqlTestCase
{
    use SmsTestHelperTrait;

    protected function setUp(): void
    {
        $this->configParams['sms_disable_trackable_urls'] = false;

        parent::setUp();
    }

    public function testSmsTokenReplacement(): void
    {
        $transport = $this->configureTwilioWithArrayTransport();
        /** @var SmsModel $smsModel */
        $smsModel  = $this->getContainer()->get(SmsModel::class);
        $this->assertInstanceOf(SmsModel::class, $smsModel);

        /** @var LeadModel $contactModel */
        $contactModel = $this->getContainer()->get(LeadModel::class);
        $this->assertInstanceOf(LeadModel::class, $contactModel);

        $page = new Page();
        $page->setTitle('Test Page');
        $page->setAlias('test-page');

        $this->em->persist($page);

        $asset = new Asset();
        $asset->setPath('test.jpg');
        $asset->setTitle('test');
        $asset->setAlias('test');

        $this->em->persist($asset);

        $contact = new Lead();
        $contact->setFirstname('John');
        $contact->setPhone('1234567890');

        $this->em->persist($contact);
        $this->em->flush();

        $sms = new Sms();
        $sms->setName('Test SMS');
        $sms->setMessage("Hello {contactfield=firstname}, download {assetlink={$asset->getId()}} or visit {pagelink={$page->getId()}} or https://mailvotech.org");

        $smsModel->saveEntity($sms);
        $smsModel->sendSms($sms, $contactModel->getEntity($contact->getId()));

        $this->assertCount(1, $transport->smses);

        $ctRegex        = 'ct=([a-zA-Z0-9%]+)';
        $domainRegex    = 'https?:\/\/([a-zA-Z0-9.-]+)';
        $assetLinkRegex = $domainRegex.'\/asset\/'.$asset->getSlug().'\?'.$ctRegex;
        $pageLinkRegex  = $domainRegex.'\/test-page\?'.$ctRegex;
        $trackingRegex  = $domainRegex.'\/r\/([a-zA-Z0-9]+)\?'.$ctRegex;

        $this->assertMatchesRegularExpression("/Hello John, download {$assetLinkRegex} or visit {$pageLinkRegex} or {$trackingRegex}/", $transport->smses[0]['content']);
    }
}
