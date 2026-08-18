<?php

declare(strict_types=1);

namespace MailVotech\MarketplaceBundle\Tests\Functional\Command;

use MailVotech\CoreBundle\Test\AbstractMailVotechTestCase;
use MailVotech\MarketplaceBundle\Api\Connection;
use MailVotech\MarketplaceBundle\Command\ListCommand;
use MailVotech\MarketplaceBundle\DTO\Allowlist as DTOAllowlist;
use MailVotech\MarketplaceBundle\Service\Allowlist;
use MailVotech\MarketplaceBundle\Service\PluginCollector;
use PHPUnit\Framework\Exception;

final class ListCommandTest extends AbstractMailVotechTestCase
{
    public function testCommand(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('getPlugins')
            ->willReturn(json_decode(file_get_contents(__DIR__.'/../../ApiResponse/list.json'), true));

        $allowlist = $this->createMock(Allowlist::class);
        $allowlist->method('getAllowlist')->willReturn(null);

        $pluginCollector = new PluginCollector($connection, $allowlist);
        $command         = new ListCommand($pluginCollector);

        $result = $this->testSymfonyCommand(
            ListCommand::NAME,
            [
                '--page'   => 1,
                '--limit'  => 5,
                '--filter' => 'mailvotech',
            ],
            $command
        );

        $expected = <<<EOF
        +--------------------------------------------------------+-----------+--------+
        | name                                                   | downloads | favers |
        +--------------------------------------------------------+-----------+--------+
        | mailvotech/mailvotech-saelos-bundle                            | 10586     | 11     |
        | koco/mailvotech-recaptcha-bundle                           | 2012      | 20     |
        |     This plugin brings reCAPTCHA integration to        |           |        |
        |     mailvotech.                                            |           |        |
        | monogramm/mailvotech-ldap-auth-bundle                      | 307       | 8      |
        |     This plugin enables LDAP authentication for        |           |        |
        |     mailvotech.                                            |           |        |
        | maatoo/mailvotech-referrals-bundle                         | 527       | 5      |
        |     This plugin enables referrals in mailvotech.           |           |        |
        | thedmsgroup/mailvotech-do-not-contact-extras-bundle        | 532       | 9      |
        |     Adds custom DNC list items to be added to standard |           |        |
        |     MailVotech DNC lists and creates phpne and sms         |           |        |
        |     channels                                           |           |        |
        +--------------------------------------------------------+-----------+--------+
        Total packages: 58
        Execution time:
        EOF;

        $this->assertStringContainsString($expected, $result->getDisplay());
        $this->assertSame(0, $result->getStatusCode());
    }

    public function testCommmandWithAllowlist(): void
    {
        $page  = 1;
        $limit = 5;
        $query = 'mailvotech';

        $plugin1 = <<<EOF
        {
            "results": [
                {
                    "name": "koco\/mailvotech-recaptcha-bundle",
                    "description": "This plugin brings reCAPTCHA integration to mailvotech.",
                    "url": "https:\/\/packagist.org\/packages\/koco\/mailvotech-recaptcha-bundle",
                    "repository": "https:\/\/github.com\/KonstantinCodes\/mailvotech-recaptcha",
                    "downloads": 2012,
                    "favers": 20
                }
            ]
        }
        EOF;

        $plugin2 = <<<EOF
        {
            "results": [
                {
                    "name": "maatoo\/mailvotech-referrals-bundle",
                    "description": "This plugin enables referrals in mailvotech.",
                    "url": "https:\/\/packagist.org\/packages\/maatoo\/mailvotech-referrals-bundle",
                    "repository": "https:\/\/github.com\/maatoo-io\/MailVotechReferralsBundle",
                    "downloads": 527,
                    "favers": 5
                }
            ]
        }
        EOF;

        $connection = $this->createMock(Connection::class);
        $matcher    = $this->exactly(2);

        $connection->expects($matcher)->method('getPlugins')->willReturnCallback(function (...$parameters) use ($matcher, $plugin1, $plugin2): array {
            if (1 === $matcher->numberOfInvocations()) {
                $this->assertSame(1, $parameters[0]);
                $this->assertSame(1, $parameters[1]);
                $this->assertSame('koco/mailvotech-recaptcha-bundle', $parameters[2]);

                return json_decode($plugin1, true);
            }
            if (2 === $matcher->numberOfInvocations()) {
                $this->assertSame(1, $parameters[0]);
                $this->assertSame(1, $parameters[1]);
                $this->assertSame('maatoo/mailvotech-referrals-bundle', $parameters[2]);

                return json_decode($plugin2, true);
            }

            throw new Exception(sprintf('Method not be called for %dth time', $matcher->numberOfInvocations()));
        });

        $allowlistPayload = DTOAllowlist::fromArray(json_decode(file_get_contents(__DIR__.'/../../ApiResponse/allowlist.json'), true));
        $allowlist        = $this->createMock(Allowlist::class);
        $allowlist->method('getAllowList')->willReturn($allowlistPayload);

        $pluginCollector = new PluginCollector($connection, $allowlist);
        $command         = new ListCommand($pluginCollector);

        $result = $this->testSymfonyCommand(
            ListCommand::NAME,
            [
                '--page'   => $page,
                '--limit'  => $limit,
                '--filter' => $query,
            ],
            $command
        );

        $expected = <<<EOF
        +-------------------------------------------------+-----------+--------+
        | name                                            | downloads | favers |
        +-------------------------------------------------+-----------+--------+
        | koco/mailvotech-recaptcha-bundle                    | 2012      | 20     |
        |     This plugin brings reCAPTCHA integration to |           |        |
        |     mailvotech.                                     |           |        |
        | maatoo/mailvotech-referrals-bundle                  | 527       | 5      |
        |     This plugin enables referrals in mailvotech.    |           |        |
        +-------------------------------------------------+-----------+--------+
        Total packages: 2
        Execution time:
        EOF;

        $this->assertStringContainsString($expected, $result->getDisplay());
        $this->assertSame(0, $result->getStatusCode());
    }
}
