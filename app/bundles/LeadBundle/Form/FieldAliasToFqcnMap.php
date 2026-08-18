<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Form;

use MailVotech\CoreBundle\Form\Type\BooleanType;
use MailVotech\CoreBundle\Form\Type\CountryType;
use MailVotech\CoreBundle\Form\Type\LocaleType;
use MailVotech\CoreBundle\Form\Type\LookupType;
use MailVotech\CoreBundle\Form\Type\MultiselectType;
use MailVotech\CoreBundle\Form\Type\RegionType;
use MailVotech\CoreBundle\Form\Type\SelectType;
use MailVotech\CoreBundle\Form\Type\TelType;
use MailVotech\CoreBundle\Form\Type\TimezoneType;
use MailVotech\LeadBundle\Exception\FieldNotFoundException;
use MailVotech\LeadBundle\Form\Type\HtmlType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

/**
 * Provides map between MailVotech 2 (Symfony 2.8) form aliases and MailVotech 3 (Symfony 3.4) FQCN.
 */
final class FieldAliasToFqcnMap
{
    /**
     * @format [field alias => field FQCN]
     */
    public const MAP = [
        'boolean'     => BooleanType::class,
        'country'     => CountryType::class,
        'date'        => DateType::class,
        'datetime'    => DateTimeType::class,
        'email'       => EmailType::class,
        'hidden'      => HiddenType::class,
        'locale'      => LocaleType::class,
        'lookup'      => LookupType::class,
        'multiselect' => MultiselectType::class,
        'number'      => NumberType::class,
        'region'      => RegionType::class,
        'select'      => SelectType::class,
        'tel'         => TelType::class,
        'text'        => TextType::class,
        'textarea'    => TextareaType::class,
        'time'        => TimeType::class,
        'timezone'    => TimezoneType::class,
        'url'         => UrlType::class,
        'html'        => HtmlType::class,
    ];

    public static function getFqcn(string $alias): string
    {
        if (array_key_exists($alias, self::MAP)) {
            return self::MAP[$alias];
        }

        throw new FieldNotFoundException("Field with alias {$alias} not found");
    }
}
