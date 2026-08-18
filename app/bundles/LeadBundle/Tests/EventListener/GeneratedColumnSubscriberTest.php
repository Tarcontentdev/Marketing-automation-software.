<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\EventListener;

use MailVotech\CoreBundle\Event\GeneratedColumnsEvent;
use MailVotech\CoreBundle\Translation\Translator;
use MailVotech\LeadBundle\Event\LeadListFiltersChoicesEvent;
use MailVotech\LeadBundle\EventListener\GeneratedColumnSubscriber;
use MailVotech\LeadBundle\Model\ListModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

final class GeneratedColumnSubscriberTest extends TestCase
{
    /**
     * @var MockObject&TranslatorInterface
     */
    private \MailVotech\CoreBundle\Translation\Translator|MockObject $translator;

    private GeneratedColumnSubscriber $generatedColumnSubscriber;

    protected function setUp(): void
    {
        parent::setUp();
        $modelTranslator = $this->createMock(Translator::class);
        $modelTranslator
            ->method('trans')
            ->willReturnArgument(0);

        $segmentModel = new class($modelTranslator) extends ListModel {
            public function __construct(Translator $translator)
            {
                $this->translator = $translator;
            }
        };

        $this->translator                = $this->createMock(TranslatorInterface::class);
        $this->generatedColumnSubscriber = new GeneratedColumnSubscriber($segmentModel, $this->translator);
    }

    public function testInGeneratedColumnsBuild(): void
    {
        $event = new GeneratedColumnsEvent();

        $this->generatedColumnSubscriber->onGeneratedColumnsBuild($event);

        $generatedColumn = $event->getGeneratedColumns()->current();

        $this->assertSame(MAILVOTECH_TABLE_PREFIX.'leads', $generatedColumn->getTableName());
        $this->assertSame('generated_email_domain', $generatedColumn->getColumnName());
        $this->assertSame('VARCHAR(255) AS (SUBSTRING(email, LOCATE("@", email) + 1)) COMMENT \'(DC2Type:generated)\'', $generatedColumn->getColumnDefinition());
    }

    public function testOnGenerateSegmentFilters(): void
    {
        $event = new LeadListFiltersChoicesEvent(
            [],
            [],
            $this->translator,
            new Request()
        );

        $this->translator->method('trans')
            ->with('mailvotech.email.segment.choice.generated_email_domain')
            ->willReturn('translated string');

        $this->generatedColumnSubscriber->onGenerateSegmentFilters($event);

        $this->assertSame([
            'label'      => 'translated string',
            'properties' => ['type' => 'text'],
            'operators'  => [
                'mailvotech.lead.list.form.operator.equals'     => '=',
                'mailvotech.lead.list.form.operator.notequals'  => '!=',
                'mailvotech.lead.list.form.operator.isempty'    => 'empty',
                'mailvotech.lead.list.form.operator.isnotempty' => '!empty',
                'mailvotech.lead.list.form.operator.islike'     => 'like',
                'mailvotech.lead.list.form.operator.isnotlike'  => '!like',
                'mailvotech.lead.list.form.operator.regexp'     => 'regexp',
                'mailvotech.lead.list.form.operator.notregexp'  => '!regexp',
                'mailvotech.core.operator.starts.with'          => 'startsWith',
                'mailvotech.core.operator.ends.with'            => 'endsWith',
                'mailvotech.core.operator.contains'             => 'contains',
            ],
            'object'    => 'lead',
            'iconClass' => 'ri-at-line',
        ], $event->getChoices()['lead']['generated_email_domain']);
    }
}
