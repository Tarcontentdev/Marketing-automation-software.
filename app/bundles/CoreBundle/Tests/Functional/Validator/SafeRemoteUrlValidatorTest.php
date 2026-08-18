<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Tests\Functional\Validator;

use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\CoreBundle\Validator\SafeRemoteUrl;
use MailVotech\CoreBundle\Validator\SafeRemoteUrlValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class SafeRemoteUrlValidatorTest extends MailVotechMysqlTestCase
{
    protected function setUp(): void
    {
        $this->configParams['validate_remote_domains'] = false;
        $this->configParams['site_url']                = 'https://site.tld';

        if ('testWithValidateRemoteDomainsEnabled' === $this->name()) {
            $this->configParams['validate_remote_domains'] = true;
            $this->configParams['allowed_remote_domains']  = ['allowed-domain.tld'];
        }

        parent::setUp();
    }

    public function testInvalidConstraint(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->expectExceptionMessageMatches('/Expected argument of type "MailVotech\\\\CoreBundle\\\\Validator\\\\SafeRemoteUrl"/');

        $validator  = new SafeRemoteUrlValidator(self::getContainer()->get(CoreParametersHelper::class));
        $validator->initialize($this->createStub(ExecutionContextInterface::class));
        $validator->validate('value', new Constraints\NotBlank());
    }

    /**
     * @return iterable<array{?string}>
     */
    public static function dataTestWithValidateRemoteDomainsDisabled(): iterable
    {
        yield 'Null' => [null];
        yield 'Empty' => [''];
        yield 'Malformed URL' => ['some'];
        yield 'Empty host' => ['https:///'];
        yield 'Regular URL' => ['https://some.tld/foo'];
    }

    #[DataProvider('dataTestWithValidateRemoteDomainsDisabled')]
    public function testWithValidateRemoteDomainsDisabled(mixed $value): void
    {
        $validator = self::getContainer()->get(ValidatorInterface::class);
        $errors    = $validator->validate($value, new SafeRemoteUrl());

        $this->assertCount(0, $errors);
    }

    /**
     * @return iterable<array{?string,bool}>
     */
    public static function dataTestWithValidateRemoteDomainsEnabled(): iterable
    {
        yield 'Null' => [null, true];
        yield 'Empty' => ['', true];
        yield 'Malformed URL' => ['some', false];
        yield 'Empty host' => ['https:///', false];
        yield 'Not in allowed domains' => ['https://some-domain.com/foo', false];
        yield 'Is in allowed domains' => ['https://allowed-domain.tld/foo', true];
        yield 'Using site URL' => ['https://site.tld/foo', true];
    }

    #[DataProvider('dataTestWithValidateRemoteDomainsEnabled')]
    public function testWithValidateRemoteDomainsEnabled(mixed $value, bool $valid): void
    {
        $validator = self::getContainer()->get(ValidatorInterface::class);
        $errors    = $validator->validate($value, new SafeRemoteUrl());

        $this->assertCount($valid ? 0 : 1, $errors);
    }
}
