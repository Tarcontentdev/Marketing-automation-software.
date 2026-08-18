<?php

declare(strict_types=1);

namespace MailVotechPlugin\GrapesJsBuilderBundle\InstallFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\PluginBundle\Entity\Integration;
use MailVotech\PluginBundle\Entity\Plugin;

final class GrapesJsData extends AbstractFixture implements OrderedFixtureInterface, FixtureGroupInterface
{
    public function __construct(
        private readonly CoreParametersHelper $coreParametersHelper,
    ) {
    }

    public static function getGroups(): array
    {
        return ['group_install', 'group_mailvotech_install_data'];
    }

    public function load(ObjectManager $manager): void
    {
        $applicationDir           = $this->coreParametersHelper->get('mailvotech.application_dir');
        $grapeJsBuilderConfigPath = $applicationDir.'/plugins/GrapesJsBuilderBundle/Config/config.php';

        if (!file_exists($grapeJsBuilderConfigPath)) {
            return;
        }

        $parameters = include $grapeJsBuilderConfigPath;

        if (!is_array($parameters)) {
            return;
        }

        $plugin = new Plugin();
        $plugin->setName($parameters['name']);
        $plugin->setDescription($parameters['description']);
        $plugin->setVersion($parameters['version']);
        $plugin->setAuthor($parameters['author']);
        $plugin->setBundle('GrapesJsBuilderBundle');
        $manager->persist($plugin);

        $integration = new Integration();
        $integration->setIsPublished(true);
        $integration->setName('GrapesJsBuilder');
        $integration->setPlugin($plugin);
        $manager->persist($integration);

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 1;
    }
}
