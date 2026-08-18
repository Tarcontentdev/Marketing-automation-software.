<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Twig;

use MailVotech\LeadBundle\Entity\DoNotContact;
use MailVotech\LeadBundle\Exception\UnknownDncReasonException;
use MailVotech\LeadBundle\Twig\Helper\DncReasonHelper;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DncReasonHelperTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array<int, string>
     */
    private array $reasonTo = [
        DoNotContact::IS_CONTACTABLE => 'mailvotech.lead.event.donotcontact_contactable',
        DoNotContact::UNSUBSCRIBED   => 'mailvotech.lead.event.donotcontact_unsubscribed',
        DoNotContact::BOUNCED        => 'mailvotech.lead.event.donotcontact_bounced',
        DoNotContact::MANUAL         => 'mailvotech.lead.event.donotcontact_manual',
    ];

    /**
     * @var array<string, string>
     */
    private array $translations = [
        'mailvotech.lead.event.donotcontact_contactable'  => 'a',
        'mailvotech.lead.event.donotcontact_unsubscribed' => 'b',
        'mailvotech.lead.event.donotcontact_bounced'      => 'c',
        'mailvotech.lead.event.donotcontact_manual'       => 'd',
    ];

    public function testToText(): void
    {
        foreach ($this->reasonTo as $reasonId => $translationKey) {
            $translationResult = $this->translations[$translationKey];

            $translator = $this->createMock(TranslatorInterface::class);
            $translator->expects($this->once())
                ->method('trans')
                ->with($translationKey)
                ->willReturn($translationResult);

            $dncReasonHelper = new DncReasonHelper($translator);

            $this->assertSame($translationResult, $dncReasonHelper->toText($reasonId));
        }

        $translator      = $this->createMock(TranslatorInterface::class);
        $dncReasonHelper = new DncReasonHelper($translator);
        $this->expectException(UnknownDncReasonException::class);
        $dncReasonHelper->toText(999);
    }

    public function testGetName(): void
    {
        $translator      = $this->createStub(TranslatorInterface::class);
        $dncReasonHelper = new DncReasonHelper($translator);
        $this->assertSame('lead_dnc_reason', $dncReasonHelper->getName());
    }
}
