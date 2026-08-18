<?php

declare(strict_types=1);

namespace MailVotech\ApiBundle\Tests\Functional\Controller;

use MailVotech\ApiBundle\Entity\oAuth2\Client;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ClientControllerTest extends MailVotechMysqlTestCase
{
    private const TOTAL_COUNT = 6;

    #[RunInSeparateProcess]
    public function testIndexActionForPager(): void
    {
        $this->createApiClients();

        // Test the first page without limits
        $this->requestCredentialsPage();
        $this->assertPaginationDetails(1);

        // Test pagination with varying limits
        $this->requestCredentialsPage(5);
        $this->assertPaginationDetails(2);
    }

    private function createApiClients(): void
    {
        foreach (range(1, self::TOTAL_COUNT) as $i) {
            $client = new Client();
            $client->setName('client'.$i);
            $client->setRedirectUris(['https://example.com/'.$i]);

            $this->em->persist($client);
        }

        $this->em->flush();
        $this->em->clear();
    }

    /**
     * Make a request to the credentials page with pagination.
     */
    private function requestCredentialsPage(?int $limit = null): void
    {
        $url = '/s/credentials?tmpl=list&name=client';
        if ($limit) {
            $url .= '&limit='.$limit;
        }

        $this->client->request(Request::METHOD_GET, $url);
    }

    /**
     * Assert the pagination details on the response.
     *
     * @param int $pageCount The expected number of pages
     */
    private function assertPaginationDetails(int $pageCount): void
    {
        $content = $this->client->getResponse()->getContent();
        $this->assertResponseIsSuccessful();

        $translator = self::getContainer()->get(TranslatorInterface::class);

        // Check for total item count in pagination
        $this->assertStringContainsString(
            $translator->trans('mailvotech.core.pagination.items', ['%count%' => self::TOTAL_COUNT]),
            (string) $content
        );

        // Check for total page count in pagination
        $this->assertStringContainsString(
            $translator->trans('mailvotech.core.pagination.pages', ['%count%' => $pageCount]),
            (string) $content
        );
    }
}
