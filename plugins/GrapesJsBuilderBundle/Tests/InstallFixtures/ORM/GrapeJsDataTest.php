<?php

declare(strict_types=1);

namespace MailVotechPlugin\GrapesJsBuilderBundle\Tests\InstallFixtures\ORM;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\PluginBundle\Entity\Integration;
use MailVotech\PluginBundle\Entity\Plugin;
use MailVotechPlugin\GrapesJsBuilderBundle\InstallFixtures\ORM\GrapesJsData;

final class GrapeJsDataTest extends MailVotechMysqlTestCase
{
    protected $useCleanupRollback = false;

    public function testGetGroups(): void
    {
        $this->assertSame(['group_install', 'group_mailvotech_install_data'], GrapesJsData::getGroups());
    }

    public function testLoad(): void
    {
        $findOneByCriteria = [
            'name'        => 'GrapesJS Builder',
            'description' => 'GrapesJS Builder with MJML support for MailVotech',
            'version'     => '1.0.0',
            'author'      => 'MailVotech Community',
            'bundle'      => 'GrapesJsBuilderBundle',
        ];
        $plugin = $this->em->getRepository(Plugin::class)->findOneBy($findOneByCriteria);
        $this->assertNotInstanceOf(Plugin::class, $plugin);

        $this->loadFixtures([GrapesJsData::class]);

        $plugin = $this->em->getRepository(Plugin::class)->findOneBy($findOneByCriteria);
        $this->assertInstanceOf(Plugin::class, $plugin);

        $integration = $this->em->getRepository(Integration::class)->findOneBy(
            [
                'isPublished' => true,
                'name'        => 'GrapesJsBuilder',
                'plugin'      => $plugin,
            ]
        );
        $this->assertInstanceOf(Integration::class, $integration);
    }
}
