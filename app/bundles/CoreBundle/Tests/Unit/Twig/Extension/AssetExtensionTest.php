<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\Twig\Extension;

use MailVotech\CoreBundle\Test\AbstractMailVotechTestCase;
use MailVotech\CoreBundle\Twig\Extension\AssetExtension;

final class AssetExtensionTest extends AbstractMailVotechTestCase
{
    public function testGetCountryFlag(): void
    {
        $assetExtension = self::getContainer()->get(AssetExtension::class);
        $this->assertInstanceOf(AssetExtension::class, $assetExtension);

        $this->assertStringStartsWith('/./app/assets/images/flags/Belgium.png', $assetExtension->getCountryFlag('Belgium'));
    }
}
