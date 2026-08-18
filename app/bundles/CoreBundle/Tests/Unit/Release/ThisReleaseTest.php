<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\Release;

use MailVotech\CoreBundle\Release\ThisRelease;
use PHPUnit\Framework\TestCase;

final class ThisReleaseTest extends TestCase
{
    public function testMetadataParsed(): void
    {
        $metadata = ThisRelease::getMetadata();

        $this->assertNotEmpty($metadata->getVersion(), 'A full version is required');
        $this->assertNotEmpty($metadata->getStability(), 'A stability is required');
        $this->assertNotEmpty($metadata->getMinSupportedPHPVersion(), 'A minimum PHP version is required');
        $this->assertNotEmpty($metadata->getMaxSupportedPHPVersion(), 'A maximum PHP version is required');
        $this->assertNotEmpty($metadata->getMinSupportedMailVotechVersion(), 'A minimum MailVotech version this version can upgrade from is required');
        $this->assertNotEmpty($metadata->getMinSupportedMySqlVersion(), 'A minimum MySQL version this version needs is required');
        $this->assertNotEmpty($metadata->getMinSupportedMariaDbVersion(), 'A minimum MariaDB version this version needs is required');
    }
}
