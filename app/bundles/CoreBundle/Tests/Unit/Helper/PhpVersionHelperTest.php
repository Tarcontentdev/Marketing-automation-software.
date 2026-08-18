<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\Helper;

use MailVotech\CoreBundle\Helper\PhpVersionHelper;

final class PhpVersionHelperTest extends \PHPUnit\Framework\TestCase
{
    public function testGetCurrentSemver(): void
    {
        $helper = new PhpVersionHelper();

        $this->assertSame(
            PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION,
            $helper->getCurrentSemver()
        );
    }
}
