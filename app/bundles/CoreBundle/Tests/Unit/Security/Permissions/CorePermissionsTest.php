<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\Security\Permissions;

use MailVotech\ApiBundle\Security\Permissions\ApiPermissions;
use MailVotech\AssetBundle\Security\Permissions\AssetPermissions;
use MailVotech\CampaignBundle\Security\Permissions\CampaignPermissions;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\UserHelper;
use MailVotech\CoreBundle\Security\Permissions\CorePermissions;
use MailVotechPlugin\MailVotechFocusBundle\Security\Permissions\FocusPermissions;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CorePermissionsTest extends \PHPUnit\Framework\TestCase
{
    private CorePermissions $corePermissions;

    /**
     * @var MockObject&CoreParametersHelper
     */
    private MockObject $coreParametersHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->coreParametersHelper = $this->createMock(CoreParametersHelper::class);
        $this->corePermissions      = new CorePermissions(
            $this->createStub(UserHelper::class),
            $this->createStub(TranslatorInterface::class),
            $this->coreParametersHelper,
            [
                $this->mockBundleArray(ApiPermissions::class),
                $this->mockBundleArray(AssetPermissions::class),
                $this->mockBundleArray(CampaignPermissions::class),
            ],
            [
                $this->mockBundleArray(FocusPermissions::class),
            ]
        );
    }

    public function testSettingPermissionObject(): void
    {
        $this->coreParametersHelper->method('all')
            ->willReturn(['parameter_a' => 'value_a']);

        $assetPermissions = new AssetPermissions($this->coreParametersHelper);
        $this->corePermissions->setPermissionObject($assetPermissions);
        $permissionObjects = $this->corePermissions->getPermissionObjects();

        // Even though the AssetPermissions object was set upfront there are
        // still 4 objects available.
        // The other three were instantiated to keep BC.
        $this->assertCount(4, $permissionObjects);

        $this->assertSame($assetPermissions, $this->corePermissions->getPermissionObject('asset'));
        $this->assertSame($assetPermissions, $this->corePermissions->getPermissionObject(AssetPermissions::class));
        $this->assertSame($permissionObjects['campaign'], $this->corePermissions->getPermissionObject(CampaignPermissions::class));
    }

    /**
     * @return array{permissionClasses: array<class-string, class-string>}
     */
    private function mockBundleArray(string $permissionClass): array
    {
        return ['permissionClasses' => [$permissionClass => $permissionClass]];
    }
}
