<?php

declare(strict_types=1);

namespace MailVotechPlugin\MailVotechFocusBundle\Tests\Twig;

use MailVotech\CoreBundle\Tests\Twig\TwigIntegrationTestTrait;
use MailVotechPlugin\MailVotechFocusBundle\Twig\Extension\FocusBundleExtension;
use Twig\Extension\ExtensionInterface;

/**
 * @see https://twig.symfony.com/doc/3.x/advanced.html#functional-tests
 */
final class TwigIntegrationTest extends \Twig\Test\IntegrationTestCase
{
    use TwigIntegrationTestTrait;

    /**
     * @return ExtensionInterface[]
     */
    public function getExtensions(): array
    {
        return [
            new FocusBundleExtension(),
        ];
    }
}
