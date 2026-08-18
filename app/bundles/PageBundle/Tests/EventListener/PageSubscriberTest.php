<?php

declare(strict_types=1);

namespace MailVotech\PageBundle\Tests\EventListener;

use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\CoreBundle\Helper\LanguageHelper;
use MailVotech\CoreBundle\Model\AuditLogModel;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\CoreBundle\Twig\Helper\AssetsHelper;
use MailVotech\PageBundle\Entity\Page;
use MailVotech\PageBundle\Event\PageBuilderEvent;
use MailVotech\PageBundle\Event\PageDisplayEvent;
use MailVotech\PageBundle\EventListener\PageSubscriber;
use MailVotech\PageBundle\Model\PageDraftModel;
use MailVotech\PageBundle\Model\PageModel;
use MailVotech\PageBundle\PageEvents;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Asset\Packages;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;

final class PageSubscriberTest extends TestCase
{
    public function testGetTokensWhenCalledReturnsValidTokens(): void
    {
        $translator       = $this->createStub(Translator::class);
        $pageBuilderEvent = new PageBuilderEvent($translator);
        $pageBuilderEvent->addToken('{token_test}', 'TOKEN VALUE');
        $tokens = $pageBuilderEvent->getTokens();
        $this->assertArrayHasKey('{token_test}', $tokens);
        $this->assertEquals('TOKEN VALUE', $tokens['{token_test}']);
    }

    public function testOnPageDisplayBodyTagRegex(): void
    {
        $dummyPageContent = <<<EOF
<html>
    <head>
    </head>
    <body class="mt-6 md:max-w-2xl p-[5px]"  onclick="myFunction()" data-help-text="téxt with nön äscii charactêrs">
    </body>
</html>
EOF;
        $event = new PageDisplayEvent(
            $dummyPageContent,
            $this->createStub(Page::class)
        );
        $dispatcher = new EventDispatcher();
        $subscriber = $this->getPageSubscriber();

        $dispatcher->addSubscriber($subscriber);

        $dispatcher->dispatch($event, PageEvents::PAGE_ON_DISPLAY);

        $this->assertSame(
            <<<EOF
<html>
    <head>
    </head>
    <body class="mt-6 md:max-w-2xl p-[5px]"  onclick="myFunction()" data-help-text="téxt with nön äscii charactêrs">
<script data-source="mailvotech">
const foo='bar';
</script>

    </body>
</html>
EOF,
            $event->getContent()
        );
    }

    /**
     * Get page subscriber with mocked dependencies.
     */
    protected function getPageSubscriber(): PageSubscriber
    {
        $assetsHelperMock   = new AssetsHelper($this->createStub(Packages::class));

        $assetsHelperMock->addScriptDeclaration("const foo='bar';", 'onPageDisplay_bodyOpen');

        return new PageSubscriber(
            $assetsHelperMock,
            $this->createStub(IpLookupHelper::class),
            $this->createStub(AuditLogModel::class),
            $this->createStub(LanguageHelper::class),
            $this->createStub(PageModel::class),
            $this->createStub(PageDraftModel::class),
        );
    }

    /**
     * Get non empty payload, having a Request and non-null entity IDs.
     *
     * @return array<string, bool|int|MockObject>
     */
    protected function getNonEmptyPayload(): array
    {
        $requestMock = $this->createMock(Request::class);

        return [
            'request' => $requestMock,
            'isNew'   => true,
            'hitId'   => 123,
            'pageId'  => 456,
            'leadId'  => 789,
        ];
    }

    /**
     * Get empty payload with all null entity IDs.
     *
     * @return array<string, null>
     */
    protected function getEmptyPayload(): array
    {
        return array_fill_keys(['request', 'isNew', 'hitId', 'pageId', 'leadId'], null);
    }
}
