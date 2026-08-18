<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Tests\Helper;

use MailVotech\FormBundle\Helper\BlockedFreeEmailProvidersHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BlockedFreeEmailProvidersHelper::class)]
final class BlockedFreeEmailProvidersHelperTest extends TestCase
{
    public function testLoadReturnsArrayFromValidJsonFile(): void
    {
        $providers = BlockedFreeEmailProvidersHelper::load();

        $this->assertIsArray($providers);
        $this->assertNotEmpty($providers);
    }

    public function testLoadReturnsArrayOfStrings(): void
    {
        $providers = BlockedFreeEmailProvidersHelper::load();

        foreach ($providers as $provider) {
            $this->assertIsString($provider);
            $this->assertNotEmpty($provider);
        }
    }

    public function testLoadReturnsNonEmptyArray(): void
    {
        $providers = BlockedFreeEmailProvidersHelper::load();

        // The JSON file should contain providers
        $this->assertGreaterThan(0, count($providers));
    }
}
