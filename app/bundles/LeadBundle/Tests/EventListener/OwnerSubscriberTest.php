<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use MailVotech\AssetBundle\Model\AssetModel;
use MailVotech\CoreBundle\Event\TokenReplacementEvent;
use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\PathsHelper;
use MailVotech\CoreBundle\Helper\ThemeHelper;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\EmailBundle\Entity\CopyRepository;
use MailVotech\EmailBundle\Event\EmailBuilderEvent;
use MailVotech\EmailBundle\Event\EmailSendEvent;
use MailVotech\EmailBundle\Helper\FromEmailHelper;
use MailVotech\EmailBundle\Helper\MailHashHelper;
use MailVotech\EmailBundle\Helper\MailHelper;
use MailVotech\EmailBundle\Helper\SMimeHelper;
use MailVotech\EmailBundle\Model\EmailStatModel;
use MailVotech\EmailBundle\MonitoredEmail\Mailbox;
use MailVotech\EmailBundle\Tests\Helper\Transport\SmtpTransport;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Entity\LeadRepository;
use MailVotech\LeadBundle\EventListener\OwnerSubscriber;
use MailVotech\PageBundle\Model\RedirectModel;
use MailVotech\PageBundle\Model\TrackableModel;
use MailVotech\UserBundle\Entity\User;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

final class OwnerSubscriberTest extends TestCase
{
    /**
     * @var array<int, array<string, int|string|null>>
     */
    private array $contacts = [
        [
            'id'        => 1,
            'email'     => 'contact1@somewhere.com',
            'firstname' => 'Contact',
            'lastname'  => '1',
            'owner_id'  => 3,
        ],
        [
            'id'        => 2,
            'email'     => 'contact2@somewhere.com',
            'firstname' => 'Contact',
            'lastname'  => '2',
            'owner_id'  => 0,
        ],
        [
            'id'        => 3,
            'email'     => 'contact3@somewhere.com',
            'firstname' => 'Contact',
            'lastname'  => '3',
            'owner_id'  => 2,
        ],
        [
            'id'        => 4,
            'email'     => 'contact4@somewhere.com',
            'firstname' => 'Contact',
            'lastname'  => '4',
            'owner_id'  => 1,
        ],
        [
            'id'        => 5,
            'email'     => 'contact5@somewhere.com',
            'firstname' => 'Contact',
            'lastname'  => '5',
            'owner_id'  => null,
        ],
    ];

    private MailHashHelper $mailHashHelper;

    protected function setUp(): void
    {
        $this->mailHashHelper       = new MailHashHelper($this->createStub(CoreParametersHelper::class));
    }

    public function testOnEmailBuild(): void
    {
        $subscriber = new OwnerSubscriber($this->getMockTranslator(), $this->getMockLeadRepository());
        $event      = new EmailBuilderEvent($this->getMockTranslator());
        $subscriber->onEmailBuild($event);

        $tokens = $event->getTokens();
        $this->assertArrayHasKey('{ownerfield=email}', $tokens);
        $this->assertArrayHasKey('{ownerfield=firstname}', $tokens);
        $this->assertArrayHasKey('{ownerfield=lastname}', $tokens);
    }

    public function testOnEmailGenerate(): void
    {
        $subscriber = new OwnerSubscriber($this->getMockTranslator(), $this->getMockLeadRepository());

        $mailer = $this->getMockMailer($this->contacts[0]);
        $event  = $this->getEmailSendEvent($mailer);
        $subscriber->onEmailGenerate($event);

        $tokens = $event->getTokens();

        $this->assertArrayNotHasKey('{ownerfield=email}', $tokens);
        $this->assertArrayHasKey('{ownerfield=firstname}', $tokens);
        $this->assertArrayHasKey('{ownerfield=lastname}', $tokens);

        $this->assertEquals('John', $tokens['{ownerfield=firstname}']);
        $this->assertEquals('S&#39;mith', $tokens['{ownerfield=lastname}']);
    }

    public function testOnEmailGenerateWithFakeOwner(): void
    {
        $subscriber = new OwnerSubscriber($this->getMockTranslator(), $this->getMockLeadRepository());

        $mailer = $this->getMockMailer($this->contacts[1]);
        $event  = $this->getEmailSendEvent($mailer);
        $subscriber->onEmailGenerate($event);

        $tokens = $event->getTokens();
        $this->assertArrayHasKey('{ownerfield=email}', $tokens);
        $this->assertArrayHasKey('{ownerfield=firstname}', $tokens);
        $this->assertArrayHasKey('{ownerfield=lastname}', $tokens);
    }

    public function testOnEmailGenerateWithNoOwner(): void
    {
        $subscriber = new OwnerSubscriber($this->getMockTranslator(), $this->getMockLeadRepository());

        $mailer = $this->getMockMailer($this->contacts[4]);
        $event  = $this->getEmailSendEvent($mailer);
        $subscriber->onEmailGenerate($event);

        $tokens = $event->getTokens();
        $this->assertArrayHasKey('{ownerfield=email}', $tokens);
        $this->assertArrayHasKey('{ownerfield=firstname}', $tokens);
        $this->assertArrayHasKey('{ownerfield=lastname}', $tokens);

        $this->assertEquals('', $tokens['{ownerfield=email}']);
        $this->assertEquals('', $tokens['{ownerfield=firstname}']);
        $this->assertEquals('', $tokens['{ownerfield=lastname}']);
    }

    public function testOnEmailDisplay(): void
    {
        $subscriber = new OwnerSubscriber($this->getMockTranslator(), $this->getMockLeadRepository());

        $mailer = $this->getMockMailer($this->contacts[0]);
        $event  = $this->getEmailSendEvent($mailer);
        $subscriber->onEmailDisplay($event);

        $tokens = $event->getTokens();
        $this->assertArrayNotHasKey('{ownerfield=email}', $tokens);
        $this->assertArrayHasKey('{ownerfield=firstname}', $tokens);
        $this->assertArrayHasKey('{ownerfield=lastname}', $tokens);
    }

    public function testOnEmailDisplayWithFakeOwner(): void
    {
        $subscriber = new OwnerSubscriber($this->getMockTranslator(), $this->getMockLeadRepository());

        $mailer = $this->getMockMailer($this->contacts[1]);
        $event  = $this->getEmailSendEvent($mailer);
        $subscriber->onEmailDisplay($event);

        $tokens = $event->getTokens();
        $this->assertArrayHasKey('{ownerfield=email}', $tokens);
        $this->assertArrayHasKey('{ownerfield=firstname}', $tokens);
        $this->assertArrayHasKey('{ownerfield=lastname}', $tokens);
    }

    public function testOnEmailDisplayWithNoOwner(): void
    {
        $subscriber = new OwnerSubscriber($this->getMockTranslator(), $this->getMockLeadRepository());

        $mailer = $this->getMockMailer($this->contacts[4]);
        $event  = $this->getEmailSendEvent($mailer);
        $subscriber->onEmailDisplay($event);

        $tokens = $event->getTokens();
        $this->assertArrayHasKey('{ownerfield=email}', $tokens);
        $this->assertArrayHasKey('{ownerfield=firstname}', $tokens);
        $this->assertArrayHasKey('{ownerfield=lastname}', $tokens);

        $this->assertEquals('', $tokens['{ownerfield=email}']);
        $this->assertEquals('', $tokens['{ownerfield=firstname}']);
        $this->assertEquals('', $tokens['{ownerfield=lastname}']);
    }

    protected function getMockLeadRepository(): LeadRepository&MockObject
    {
        $mockLeadRepository = $this->createMock(LeadRepository::class);

        $mockLeadRepository->expects($this->atLeast(0))->method('getLeadOwner')
            ->willReturnMap(
                [
                    [1, ['id' => 1, 'email' => 'owner1@owner.com', 'first_name' => '', 'last_name' => '', 'signature' => 'owner 1']],
                    [2, ['id' => 2, 'email' => 'owner2@owner.com', 'first_name' => '', 'last_name' => '', 'signature' => 'owner 2']],
                    [3, ['id' => 3, 'email' => 'owner3@owner.com', 'first_name' => 'John', 'last_name' => 'S&#39;mith', 'signature' => 'owner 2']],
                ]
            );

        return $mockLeadRepository;
    }

    /**
     * @param mixed[] $parameterMap
     */
    private function getMockParametersHelper(bool $mailIsOwner = true, array $parameterMap = []): MockObject&CoreParametersHelper
    {
        $coreParametersHelper = $this->createMock(CoreParametersHelper::class);

        $parameterMap = array_merge(
            [
                ['mailer_return_path', false, null],
                ['mailer_is_owner', false, $mailIsOwner],
            ],
            $parameterMap
        );

        $coreParametersHelper->method('get')
            ->willReturnMap(
                $parameterMap
            );

        return $coreParametersHelper;
    }

    /**
     * @param array<string, mixed> $lead
     */
    protected function getMockMailer(array $lead): MailHelper
    {
        $parameterMap = [
            ['mailer_custom_headers', [], ['X-MailVotech-Test' => 'test', 'X-MailVotech-Test2' => 'test']],
        ];

        $coreParametersHelper = $this->getMockParametersHelper(true, $parameterMap);

        $coreParametersHelper->expects($this->atLeast(1))->method('get')
            ->willReturnMap(
                [
                    ['mailer_custom_headers', [], ['X-MailVotech-Test' => 'test', 'X-MailVotech-Test2' => 'test']],
                ]
            );

        $themeHelper = $this->createMock(ThemeHelper::class);
        $themeHelper->expects($this->never())
            ->method('checkForTwigTemplate');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->never()) // Never to make sure that the mock is properly tested if needed.
            ->method('getReference');

        $transport    = new SmtpTransport();
        $mailer       = new Mailer($transport);
        $requestStack = new RequestStack();
        $mailerHelper = new MailHelper(
            $mailer,
            $this->createStub(FromEmailHelper::class),
            $coreParametersHelper,
            $this->createStub(Mailbox::class),
            $this->createStub(LoggerInterface::class),
            $this->mailHashHelper,
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
        $mailerHelper->setLead($lead);

        return $mailerHelper;
    }

    /**
     * @return Translator&MockObject
     */
    protected function getMockTranslator()
    {
        /** @var Translator&MockObject $translator */
        $translator = $this->createMock(Translator::class);
        $translator
            ->method('hasId')
            ->willReturn(false);

        return $translator;
    }

    public function testOnSmsTokenReplacement(): void
    {
        foreach ($this->onSmsTokenReplacementProvider() as $data) {
            [$content, $expected, $lead] = $data;

            $leadRepository = $this->createMock(LeadRepository::class);
            $leadRepository->method('getLeadOwner')->willReturn(['first_name' => 'John', 'last_name' => 'Doe']);
            $subscriber = new OwnerSubscriber($this->createStub(TranslatorInterface::class), $leadRepository);

            $event = new TokenReplacementEvent($content, $lead);
            $subscriber->onSmsTokenReplacement($event);
            $this->assertEquals($expected, $event->getContent());
        }
    }

    protected function getUser(): User
    {
        $user = new class() extends User {
            public function setId(int $id): void
            {
                $this->id = $id;
            }
        };
        $user->setId(1);
        $user->setFirstName('John');
        $user->setLastName('Doe');

        return $user;
    }

    /**
     * @return array<mixed>
     */
    private function onSmsTokenReplacementProvider(): array
    {
        $lead = $this->createMock(Lead::class);
        $lead
            ->method('getId')
            ->willReturn(1);
        $lead
            ->method('getProfileFields')
            ->willReturn(
                [
                    'id'     => 1,
                ]
            );
        $lead
            ->method('getowner')
            ->willReturn(
                $this->getUser()
            );
        $user = $this->getUser();
        $lead->setOwner($user);
        $validOwner = [
            'Hello {ownerfield=firstname} {ownerfield=lastname}',
            'Hello John Doe',
            $lead,
        ];

        $noOwner = [
            'Hello {ownerfield=firstname} {ownerfield=lastname}',
            'Hello  ',
            new Lead(),
        ];

        return [
            $validOwner,
            $noOwner,
        ];
    }

    protected function getEmailSendEvent(MailHelper $mailer): EmailSendEvent
    {
        $event = new EmailSendEvent($mailer);
        $event->setContent('<html><body>{ownerfield=firstname} {ownerfield=lastname}</body></html>');

        return $event;
    }
}
