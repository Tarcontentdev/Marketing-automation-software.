<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\AssetBundle\Model\AssetModel;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\IpLookupHelper;
use MailVotech\CoreBundle\Helper\PathsHelper;
use MailVotech\CoreBundle\Helper\ThemeHelper;
use MailVotech\CoreBundle\Model\AuditLogModel;
use MailVotech\EmailBundle\Entity\CopyRepository;
use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Entity\EmailRepository;
use MailVotech\EmailBundle\Entity\Stat;
use MailVotech\EmailBundle\Event\EmailSendEvent;
use MailVotech\EmailBundle\Event\QueueEmailEvent;
use MailVotech\EmailBundle\EventListener\EmailSubscriber;
use MailVotech\EmailBundle\Helper\FromEmailHelper;
use MailVotech\EmailBundle\Helper\MailHashHelper;
use MailVotech\EmailBundle\Helper\MailHelper;
use MailVotech\EmailBundle\Helper\SMimeHelper;
use MailVotech\EmailBundle\Mailer\Message\MailVotechMessage;
use MailVotech\EmailBundle\Model\EmailDraftModel;
use MailVotech\EmailBundle\Model\EmailModel;
use MailVotech\EmailBundle\Model\EmailStatModel;
use MailVotech\EmailBundle\MonitoredEmail\Mailbox;
use MailVotech\EmailBundle\Tests\Helper\Transport\BatchTransport;
use MailVotech\PageBundle\Model\RedirectModel;
use MailVotech\PageBundle\Model\TrackableModel;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class EmailSubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MockObject&EmailModel
     */
    private MockObject $emailModel;

    /**
     * @var MockObject&MailVotechMessage
     */
    private MockObject $mockMessage;

    private EmailSubscriber $subscriber;

    protected function setup(): void
    {
        parent::setUp();
        $this->emailModel       = $this->createMock(EmailModel::class);
        $this->mockMessage      = $this->createMock(MailVotechMessage::class);
        $this->subscriber       = new EmailSubscriber($this->createStub(IpLookupHelper::class), $this->createStub(AuditLogModel::class), $this->emailModel, $this->createStub(TranslatorInterface::class), $this->createStub(EntityManagerInterface::class), $this->createStub(EmailDraftModel::class), $this->createStub(EmailRepository::class));
    }

    public function testOnEmailResendWithNoLeadIdHash(): void
    {
        $event = new QueueEmailEvent($this->mockMessage);

        $this->emailModel->expects($this->never())
            ->method('getEmailStatus');

        $this->subscriber->onEmailResend($event);

        $this->assertFalse($event->shouldTryAgain());
    }

    public function testOnEmailResendWithNoStat(): void
    {
        $message = new class() extends MailVotechMessage {
            public ?string $leadIdHash = 'some-hash';
        };

        $event = new QueueEmailEvent($message);

        $this->emailModel->expects($this->once())
            ->method('getEmailStatus');

        $this->emailModel->expects($this->never())
            ->method('saveEmailStat');

        $this->emailModel->expects($this->never())
            ->method('setDoNotContact');

        $this->subscriber->onEmailResend($event);

        $this->assertFalse($event->shouldTryAgain());
    }

    public function testOnEmailResendWithNoRetry(): void
    {
        $message = new class() extends MailVotechMessage {
            public ?string $leadIdHash = 'some-hash';
        };

        $event = new QueueEmailEvent($message);
        $stat  = new Stat();

        $this->emailModel->expects($this->once())
            ->method('getEmailStatus')
            ->willReturn($stat);

        $this->emailModel->expects($this->once())
            ->method('saveEmailStat')
            ->with($stat);

        $this->emailModel->expects($this->never())
            ->method('setDoNotContact');

        $this->subscriber->onEmailResend($event);

        $this->assertSame(1, $stat->getRetryCount());
        $this->assertTrue($event->shouldTryAgain());
    }

    public function testOnEmailResendWhenShouldTryAgain(): void
    {
        $this->mockMessage->method('getLeadIdHash')
            ->willReturn('idhash');

        $queueEmailEvent = new QueueEmailEvent($this->mockMessage);

        $stat = new Stat();
        $stat->setRetryCount(2);

        $this->emailModel->expects($this->once())
            ->method('getEmailStatus')
            ->willReturn($stat);

        $this->subscriber->onEmailResend($queueEmailEvent);
        $this->assertTrue($queueEmailEvent->shouldTryAgain());
    }

    public function testOnEmailResendWhenShouldNotTryAgain(): void
    {
        $this->mockMessage
            ->method('getLeadIdHash')
            ->willReturn('idhash');

        $this->mockMessage->expects($this->once())
            ->method('getSubject')
            ->willReturn('Subject');

        $queueEmailEvent = new QueueEmailEvent($this->mockMessage);

        $stat = new Stat();
        $stat->setRetryCount(3);

        $this->emailModel->expects($this->once())
            ->method('getEmailStatus')
            ->willReturn($stat);

        $this->subscriber->onEmailResend($queueEmailEvent);
        $this->assertFalse($queueEmailEvent->shouldTryAgain());
    }

    public function testOnEmailResendWith4Retry(): void
    {
        $message = new class() extends MailVotechMessage {
            public ?string $leadIdHash = 'some-hash';
        };

        $message->subject('Subject');

        $event = new QueueEmailEvent($message);
        $stat  = new Stat();

        $stat->setRetryCount(4);

        $this->emailModel->expects($this->once())
            ->method('getEmailStatus')
            ->willReturn($stat);

        $this->emailModel->expects($this->once())
            ->method('saveEmailStat')
            ->with($stat);

        $this->emailModel->expects($this->once())
            ->method('setDoNotContact')
            ->with($stat);

        $this->subscriber->onEmailResend($event);

        $this->assertSame(5, $stat->getRetryCount());
        $this->assertFalse($event->shouldTryAgain());
    }

    public function testOnEmailSendAddPreheaderText(): void
    {
        $this->runPreheaderEvent(
            <<<'CONTENT'
<html xmlns="http://www.w3.org/1999/xhtml">
    <body style="margin: 0px; cursor: auto;" class="ui-sortable">
        <div data-section-wrapper="1">
            <center>
                <table data-section="1" style="width: 600;" width="600" cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr>
                            <td>
                                <div data-slot-container="1" style="min-height: 30px">
                                    <div data-slot="text"><br /><h2>Hello there!</h2><br />{test} test We haven't heard from you for a while...<a href="https://google.com">check this link</a><br /><br />{unsubscribe_text} | {webview_text}</div>{dynamiccontent="Dynamic Content 2"}<div data-slot="codemode">
                                    <div id="codemodeHtmlContainer">
    <p>Place your content here {test}</p></div>

                                </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </center>
        </div>
</body></html>
CONTENT,
            function (string $content): void {
                $preheaderTextHtml = EmailSubscriber::PREHEADER_HTML_ELEMENT_BEFORE.'this is a nice preheader text'.EmailSubscriber::PREHEADER_HTML_ELEMENT_AFTER;
                $this->assertStringContainsString($preheaderTextHtml, $content);
                $this->assertMatchesRegularExpression(EmailSubscriber::PREHEADER_HTML_SEARCH_PATTERN, $content);
            }
        );
    }

    public function testOnEmailSendAddPreheaderTextWithPreheaderPresent(): void
    {
        $this->runPreheaderEvent(
            <<<'CONTENT'
<html xmlns="http://www.w3.org/1999/xhtml">
    <body style="margin: 0px; cursor: auto;" class="ui-sortable">
        <div class="preheader" style="font-size:1px;line-height:1px;display:none;color:#fff;max-height:0;max-width:0;opacity:0;overflow:hidden">Original Preheader here</div>
        <div data-section-wrapper="1">
            <center>
                <table data-section="1" style="width: 600;" width="600" cellpadding="0" cellspacing="0">
                    <tbody>
                        <tr>
                            <td>
                                <div data-slot-container="1" style="min-height: 30px">
                                    <div data-slot="text"><br /><h2>Hello there!</h2><br />{test} test We haven't heard from you for a while...<a href="https://google.com">check this link</a><br /><br />{unsubscribe_text} | {webview_text}</div>{dynamiccontent="Dynamic Content 2"}<div data-slot="codemode">
                                    <div id="codemodeHtmlContainer"><p>Place your content here {test}</p></div>
                                </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </center>
        </div>
</body></html>
CONTENT,

            function (string $content): void {
                $preheaderTextHtml = EmailSubscriber::PREHEADER_HTML_ELEMENT_BEFORE.'this is a nice preheader text'.EmailSubscriber::PREHEADER_HTML_ELEMENT_AFTER;
                $this->assertStringContainsString($preheaderTextHtml, $content);
                $this->assertStringNotContainsString('Original Preheader here', $content);
                $this->assertMatchesRegularExpression(EmailSubscriber::PREHEADER_HTML_SEARCH_PATTERN, $content);
            }
        );
    }

    private function runPreheaderEvent(string $html, callable $assert): void
    {
        /** @var MockObject&CoreParametersHelper $coreParametersHelper */
        $coreParametersHelper = $this->createMock(CoreParametersHelper::class);

        $themeHelper = $this->createMock(ThemeHelper::class);
        $themeHelper->expects($this->never())
            ->method('checkForTwigTemplate');

        $coreParametersHelper->method('get')
            ->willReturnMap(
                [
                    ['mailer_from_email', null, 'nobody@nowhere.com'],
                    ['mailer_from_name', null, 'No Body'],
                ]
            );
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never()) // Never to make sure that the mock is properly tested if needed.
            ->method('getReference');

        $mailer       = new Mailer(new BatchTransport());
        $requestStack = new RequestStack();
        $mailHelper   = new MailHelper(
            $mailer,
            $this->createStub(FromEmailHelper::class),
            $coreParametersHelper,
            $this->createStub(Mailbox::class),
            new NullLogger(),
            new MailHashHelper($coreParametersHelper),
            $this->createStub(RouterInterface::class),
            $this->createStub(Environment::class),
            $themeHelper,
            $this->createStub(PathsHelper::class),
            $this->createStub(EventDispatcherInterface::class),
            $requestStack,
            $entityManager,
            $this->createStub(AssetModel::class),
            $this->createStub(TrackableModel::class),
            $this->createStub(RedirectModel::class),
            $this->createStub(SMimeHelper::class),
            $this->createStub(EmailStatModel::class),
            $this->createStub(CopyRepository::class),
        );

        $email = new Email();
        $email->setCustomHtml($html);
        $email->setPreheaderText('this is a nice preheader text');
        $mailHelper->setEmail($email);

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($this->subscriber);

        $event = new EmailSendEvent($mailHelper);

        $this->subscriber->onEmailSendAddPreheaderText($event);

        $assert($event->getContent());
    }
}
