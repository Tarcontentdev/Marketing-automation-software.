<?php

namespace MailVotech\LeadBundle\Segment;

use Symfony\Contracts\Translation\TranslatorInterface;

class RelativeDate
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function getRelativeDateStrings(): array
    {
        $keys = $this->getRelativeDateTranslationKeys();

        $strings = [];
        foreach ($keys as $key) {
            $strings[$key] = $this->translator->trans($key);
        }

        return $strings;
    }

    private function getRelativeDateTranslationKeys(): array
    {
        return [
            'mailvotech.lead.list.month_last',
            'mailvotech.lead.list.month_next',
            'mailvotech.lead.list.month_this',
            'mailvotech.lead.list.today',
            'mailvotech.lead.list.tomorrow',
            'mailvotech.lead.list.yesterday',
            'mailvotech.lead.list.week_last',
            'mailvotech.lead.list.week_next',
            'mailvotech.lead.list.week_this',
            'mailvotech.lead.list.year_last',
            'mailvotech.lead.list.year_next',
            'mailvotech.lead.list.year_this',
            'mailvotech.lead.list.anniversary',
        ];
    }
}
