<?php

declare(strict_types=1);

namespace MailVotech\CampaignBundle\Tests\Functional\Campaign;

use MailVotech\CampaignBundle\Entity\Campaign;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;

final class DetailsTest extends MailVotechMysqlTestCase
{
    public function testDetailsPageLoadCorrectly(): void
    {
        $campaign = new Campaign();
        $campaign->setName('Campaign A');
        $campaign->setCanvasSettings([
            'nodes' => [
                0 => [
                    'id'        => '148',
                    'positionX' => '760',
                    'positionY' => '155',
                ],
                1 => [
                    'id'        => 'lists',
                    'positionX' => '860',
                    'positionY' => '50',
                ],
            ],
            'connections' => [
                0 => [
                    'sourceId' => 'lists',
                    'targetId' => '148',
                    'anchors'  => [
                        'source' => 'leadsource',
                        'target' => 'top',
                    ],
                ],
            ],
        ]
        );
        $this->em->persist($campaign);
        $this->em->flush();

        $this->client->request('GET', sprintf('/s/campaigns/view/%s', $campaign->getId()));

        $response = $this->client->getResponse();
        self::assertResponseIsSuccessful();
        $this->assertStringContainsString($campaign->getName(), (string) $response->getContent());
        $this->assertStringContainsString(sprintf('data-target-url="/s/campaigns/view/%s/contact/1"', $campaign->getId()), (string) $response->getContent());
    }
}
