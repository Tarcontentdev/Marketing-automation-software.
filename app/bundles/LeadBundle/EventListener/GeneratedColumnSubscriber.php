<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\EventListener;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\Doctrine\GeneratedColumn\GeneratedColumn;
use MailVotech\CoreBundle\Event\GeneratedColumnsEvent;
use MailVotech\LeadBundle\Event\LeadListFiltersChoicesEvent;
use MailVotech\LeadBundle\LeadEvents;
use MailVotech\LeadBundle\Model\ListModel;
use MailVotech\LeadBundle\Segment\SegmentFilterIconTrait;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class GeneratedColumnSubscriber implements EventSubscriberInterface
{
    use SegmentFilterIconTrait;

    public function __construct(
        private ListModel $segmentModel,
        private TranslatorInterface $translator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            CoreEvents::ON_GENERATED_COLUMNS_BUILD       => ['onGeneratedColumnsBuild', 0],
            LeadEvents::LIST_FILTERS_CHOICES_ON_GENERATE => ['onGenerateSegmentFilters', 0],
        ];
    }

    public function onGeneratedColumnsBuild(GeneratedColumnsEvent $event): void
    {
        $emailDomain = new GeneratedColumn(
            'leads',
            'generated_email_domain',
            'VARCHAR(255)',
            'SUBSTRING(email, LOCATE("@", email) + 1)'
        );

        $event->addGeneratedColumn($emailDomain);
    }

    public function onGenerateSegmentFilters(LeadListFiltersChoicesEvent $event): void
    {
        $event->addChoice('lead', 'generated_email_domain', [
            'label'      => $this->translator->trans('mailvotech.email.segment.choice.generated_email_domain'),
            'properties' => ['type' => 'text'],
            'operators'  => $this->segmentModel->getOperatorsForFieldType(
                [
                    'include' => [
                        '=',
                        '!=',
                        'empty',
                        '!empty',
                        'like',
                        '!like',
                        'regexp',
                        '!regexp',
                        'startsWith',
                        'endsWith',
                        'contains',
                    ],
                ]
            ),
            'object'    => 'lead',
            'iconClass' => $this->getSegmentFilterIcon('generated_email_domain'),
        ]);
    }
}
