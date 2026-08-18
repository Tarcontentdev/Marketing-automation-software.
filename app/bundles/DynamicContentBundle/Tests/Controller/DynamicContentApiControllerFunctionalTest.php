<?php

declare(strict_types=1);

namespace MailVotech\DynamicContentBundle\Tests\Controller;

use MailVotech\CoreBundle\Helper\ClickthroughHelper;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\DynamicContentBundle\Entity\DynamicContent;
use MailVotech\DynamicContentBundle\Entity\DynamicContentLeadData;
use MailVotech\EmailBundle\Entity\Stat;
use MailVotech\LeadBundle\Entity\Lead;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[PreserveGlobalState(false)]
#[RunTestsInSeparateProcesses]
final class DynamicContentApiControllerFunctionalTest extends MailVotechMysqlTestCase
{
    public function testDwcGetEndpointForNoSlotNorContact(): void
    {
        $this->client->request(Request::METHOD_GET, '/dwc/slot-a');

        self::assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getContent());
    }

    public function testDwcGetEndpointForASlotAndContact(): void
    {
        $contact = new Lead();
        $contact->setEmail('johana@doe.email');

        $dwc = new DynamicContent();
        $dwc->setContent('<some>content</some>');
        $dwc->setName('Slot A');
        $dwc->setSlotName('slot-a');

        $dwcContact = new DynamicContentLeadData();
        $dwcContact->setDateAdded(new \DateTime());
        $dwcContact->setDynamicContent($dwc);
        $dwcContact->setLead($contact);
        $dwcContact->setSlot($dwc->getSlotName());

        $stat = new Stat();
        $stat->setLead($contact);
        $stat->setTrackingHash('tracking-hash-1');
        $stat->setEmailAddress($contact->getEmail());
        $stat->setDateSent(new \DateTime());

        $this->em->persist($contact);
        $this->em->persist($stat);
        $this->em->persist($dwc);
        $this->em->persist($dwcContact);
        $this->em->flush();

        $ct = ClickthroughHelper::encodeArrayForUrl(['stat' => 'tracking-hash-1']);

        $this->client->request(Request::METHOD_GET, "/dwc/slot-a?ct={$ct}");

        self::assertResponseIsSuccessful($this->client->getResponse()->getContent());

        $responseArray = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('<some>content</some>', $responseArray['content']);
    }

    public function testCreateDwc(): void
    {
        $payload = [
            'name'    => 'API test',
            'content' => 'API test',
        ];

        $this->client->request(Request::METHOD_POST, '/api/dynamiccontents/new', $payload);
        self::assertResponseStatusCodeSame(Response::HTTP_CREATED, $this->client->getResponse()->getContent());
    }
}
