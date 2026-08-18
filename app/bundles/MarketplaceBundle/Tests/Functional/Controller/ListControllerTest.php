<?php

declare(strict_types=1);

namespace MailVotech\MarketplaceBundle\Tests\Functional\Controller;

use GuzzleHttp\Psr7\Response;
use MailVotech\CoreBundle\Test\Guzzle\ClientMockTrait;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\MarketplaceBundle\Service\Allowlist;
use MailVotech\MarketplaceBundle\Service\Config;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ListControllerTest extends MailVotechMysqlTestCase
{
    use ClientMockTrait;

    protected function setUp(): void
    {
        if ('testMarketplaceListTableWithNoAllowList' === $this->name()) {
            $this->configParams[Config::MARKETPLACE_ALLOWLIST_URL] = '0'; // Empty string results in null for some reason.
        }

        parent::setUp();
    }

    public function testMarketplaceListTableWithNoAllowList(): void
    {
        $handlerStack = $this->getClientMockHandler();
        $handlerStack->append(
            new Response(SymfonyResponse::HTTP_OK, [], file_get_contents(__DIR__.'/../../ApiResponse/list.json'))  // Getting the package list from Packagist API.
        );

        /** @var Allowlist $allowlist */
        $allowlist = self::getContainer()->get(Allowlist::class);
        $allowlist->clearCache();

        $crawler = $this->client->request('GET', 's/marketplace');

        self::assertResponseIsSuccessful($this->client->getResponse()->getContent());

        $this->assertSame([
            'MailVotech Saelos Bundle',
            'MailVotech Recaptcha Bundle',
            'MailVotech Ldap Auth Bundle',
            'MailVotech Referrals Bundle',
            'MailVotech Do Not Contact Extras Bundle',
        ], array_map(
            trim(...),
            $crawler->filter('#marketplace-packages-table .package-name a')->extract(['_text'])
        ));
    }

    public function testMarketplaceListTableWithAllowList(): void
    {
        $mockResults = json_decode(file_get_contents(__DIR__.'/../../ApiResponse/list.json'), true)['results'];

        $handlerStack = $this->getClientMockHandler();
        $handlerStack->append(
            new Response(SymfonyResponse::HTTP_OK, [], file_get_contents(__DIR__.'/../../ApiResponse/allowlist.json')), // Getting Allow list from Github API.
            new Response(SymfonyResponse::HTTP_OK, [], json_encode(['results' => [$mockResults[1]]])), // mailvotech-recaptcha-bundle
            new Response(SymfonyResponse::HTTP_OK, [], json_encode(['results' => [$mockResults[3]]])), // mailvotech-referrals-bundle
        );

        /** @var Allowlist $allowlist */
        $allowlist = self::getContainer()->get(Allowlist::class);
        $allowlist->clearCache();

        $crawler = $this->client->request('GET', 's/marketplace');

        self::assertResponseIsSuccessful();

        $this->assertSame([
            'KocoCaptcha',
            'MailVotech Referrals Bundle',
        ], array_map(
            trim(...),
            $crawler->filter('#marketplace-packages-table .package-name a')->extract(['_text'])
        ));
    }
}
