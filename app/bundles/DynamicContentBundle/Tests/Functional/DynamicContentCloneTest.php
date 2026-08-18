<?php

declare(strict_types=1);

namespace MailVotech\DynamicContentBundle\Tests\Functional;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\DynamicContentBundle\Entity\DynamicContent;
use Symfony\Component\HttpFoundation\Request;

final class DynamicContentCloneTest extends MailVotechMysqlTestCase
{
    public function testCloneActionForDynamicContent(): void
    {
        $dwc = $this->createDynamicContent('Original DWC');
        $this->em->flush();

        $this->client->request(
            Request::METHOD_GET,
            sprintf('/s/dwc/clone/%d', $dwc->getId())
        );

        $this->assertResponseIsSuccessful();

        $this->assertStringContainsString($dwc->getName(), (string) $this->client->getResponse()->getContent());
    }

    public function testCloneActionForTranslatedDynamicContent(): void
    {
        $dwcParent = $this->createDynamicContent('Original DWC');

        $dwc = $this->createDynamicContent('Original fr', 'fr');
        $dwc->setTranslationParent($dwcParent);
        $this->em->persist($dwc);

        $this->em->flush();

        $this->client->request(
            Request::METHOD_GET,
            sprintf('/s/dwc/clone/%d', $dwc->getId())
        );

        $this->assertResponseIsSuccessful();

        $this->assertStringContainsString($dwc->getName(), (string) $this->client->getResponse()->getContent());
    }

    private function createDynamicContent(string $name, string $language = 'en'): DynamicContent
    {
        $dwc = new DynamicContent();
        $dwc->setName($name);
        $dwc->setLanguage($language);
        $this->em->persist($dwc);

        return $dwc;
    }
}
