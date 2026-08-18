<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Form;

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
use MailVotech\LeadBundle\Form\FieldAliasToFqcnMap;
use MailVotech\LeadBundle\Form\Type\HtmlType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;

final class FieldAliasToFqcnMapTest extends TestCase
{
    #[DataProvider('aliasFqcnProvider')]
    public function testGetFqcn(string $alias, string $fcqn): void
    {
        $this->assertSame(FieldAliasToFqcnMap::getFqcn($alias), $fcqn);
    }

    public function testGetFqcnInvalid(): void
    {
        $alias = 'invalid_type';
        $this->expectException(FieldNotFoundException::class);
        $this->expectExceptionMessage("Field with alias {$alias} not found");
        FieldAliasToFqcnMap::getFqcn($alias);
    }

    /**
     * @return \Iterator<(int|string), mixed>
     */
    public static function aliasFqcnProvider(): \Iterator
    {
        yield ['boolean', BooleanType::class];
        yield ['country', CountryType::class];
        yield ['date', DateType::class];
        yield ['datetime', DateTimeType::class];
        yield ['email', EmailType::class];
        yield ['hidden', HiddenType::class];
        yield ['locale', LocaleType::class];
        yield ['lookup', LookupType::class];
        yield ['multiselect', MultiselectType::class];
        yield ['number', NumberType::class];
        yield ['region', RegionType::class];
        yield ['select', SelectType::class];
        yield ['tel', TelType::class];
        yield ['text', TextType::class];
        yield ['textarea', TextareaType::class];
        yield ['time', TimeType::class];
        yield ['timezone', TimezoneType::class];
        yield ['url', UrlType::class];
        yield ['html', HtmlType::class];
    }
}
