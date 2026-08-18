<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Unit\Update\Step;

use MailVotech\CoreBundle\Helper\CacheHelper;
use MailVotech\CoreBundle\Update\Step\DeleteCacheStep;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DeleteCacheStepTest extends AbstractStepTestCase
{
    /**
     * @var MockObject&CacheHelper
     */
    private MockObject $cacheHelper;

    /**
     * @var MockObject&TranslatorInterface
     */
    private MockObject $translator;

    private DeleteCacheStep $step;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheHelper = $this->createMock(CacheHelper::class);
        $this->translator  = $this->createMock(TranslatorInterface::class);
        $this->step        = new DeleteCacheStep($this->cacheHelper, $this->translator);
    }

    public function testCacheIsNukedAndProgressNoted(): void
    {
        $stepOutput = 'mailvotech.core.update.clear.cache';
        $this->translator->expects($this->once())
            ->method('trans')
            ->with($stepOutput)
            ->willReturn($stepOutput);

        $this->cacheHelper->expects($this->once())
            ->method('nukeCache');

        $this->step->execute($this->progressBar, $this->input, $this->output);

        $this->assertEquals($stepOutput, $this->progressBar->getMessage());
    }
}
