<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\Scheduler\Enum;

final class SchedulerEnum
{
    public const UNIT_NOW     = 'NOW';

    public const UNIT_DAILY   = 'DAILY';

    public const UNIT_WEEKLY  = 'WEEKLY'; // Defined in report.js too

    public const UNIT_MONTHLY = 'MONTHLY'; // Defined in report.js too

    public const DAY_MO        = 'MO';

    public const DAY_TU        = 'TU';

    public const DAY_WE        = 'WE';

    public const DAY_TH        = 'TH';

    public const DAY_FR        = 'FR';

    public const DAY_SA        = 'SA';

    public const DAY_SU        = 'SU';

    public const DAY_WEEK_DAYS = 'WEEK_DAYS';

    public const MONTH_FREQUENCY_FIRST = '1';

    public const MONTH_FREQUENCY_LAST  = '-1';

    public static function getUnitEnumForSelect(): array
    {
        return [
            'mailvotech.report.schedule.unit.now'   => self::UNIT_NOW,
            'mailvotech.report.schedule.unit.day'   => self::UNIT_DAILY,
            'mailvotech.report.schedule.unit.week'  => self::UNIT_WEEKLY,
            'mailvotech.report.schedule.unit.month' => self::UNIT_MONTHLY,
        ];
    }

    public static function getDayEnumForSelect(): array
    {
        return [
            'mailvotech.report.schedule.day.monday'    => self::DAY_MO,
            'mailvotech.report.schedule.day.tuesday'   => self::DAY_TU,
            'mailvotech.report.schedule.day.wednesday' => self::DAY_WE,
            'mailvotech.report.schedule.day.thursday'  => self::DAY_TH,
            'mailvotech.report.schedule.day.friday'    => self::DAY_FR,
            'mailvotech.report.schedule.day.saturday'  => self::DAY_SA,
            'mailvotech.report.schedule.day.sunday'    => self::DAY_SU,
            'mailvotech.report.schedule.day.week_days' => self::DAY_WEEK_DAYS,
        ];
    }

    public static function getMonthFrequencyForSelect(): array
    {
        return [
            'mailvotech.report.schedule.month_frequency.first' => self::MONTH_FREQUENCY_FIRST,
            'mailvotech.report.schedule.month_frequency.last'  => self::MONTH_FREQUENCY_LAST,
        ];
    }

    public static function getWeekDays(): array
    {
        return [
            self::DAY_MO,
            self::DAY_TU,
            self::DAY_WE,
            self::DAY_TH,
            self::DAY_FR,
        ];
    }
}
