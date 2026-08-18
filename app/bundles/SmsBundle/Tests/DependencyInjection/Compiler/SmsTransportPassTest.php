<?php

declare(strict_types=1);

namespace MailVotech\SmsBundle\Tests\DependencyInjection\Compiler;

use MailVotech\PluginBundle\Helper\IntegrationHelper;
use MailVotech\SmsBundle\DependencyInjection\Compiler\SmsTransportPass;
use MailVotech\SmsBundle\Sms\TransportChain;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class SmsTransportPassTest extends TestCase
{
    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container->addCompilerPass(new SmsTransportPass());
        $container
            ->register('foo')
            ->setPublic(true)
            ->setAbstract(true)
            ->addTag('mailvotech.sms_transport', ['alias'=>'fakeAliasDefault', 'integrationAlias' => 'fakeIntegrationDefault']);

        $container
            ->register('chocolate')
            ->setPublic(true)
            ->setAbstract(true);

        $container
            ->register('bar')
            ->setPublic(true)
            ->setAbstract(true)
            ->addTag('mailvotech.sms_transport');

        $transport = $this->getMockBuilder(TransportChain::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['addTransport'])
            ->getMock();

        $container
            ->register('mailvotech.sms.transport_chain')
            ->setClass($transport::class)
            ->setArguments(['foo', $this->createStub(IntegrationHelper::class)])
            ->setShared(false)
            ->setSynthetic(true)
            ->setAbstract(true);

        $pass = new SmsTransportPass();
        $pass->process($container);

        $this->assertCount(2, $container->findTaggedServiceIds('mailvotech.sms_transport'));

        $methodCalls = $container->getDefinition('mailvotech.sms.transport_chain')->getMethodCalls();
        $this->assertCount(count($methodCalls), $container->findTaggedServiceIds('mailvotech.sms_transport'));

        // Translation string
        $this->assertEquals('fakeAliasDefault', $methodCalls[0][1][2]);
        // Integration name/alias
        $this->assertEquals('fakeIntegrationDefault', $methodCalls[0][1][3]);

        // Translation string is set as service ID by default
        $this->assertEquals('bar', $methodCalls[1][1][2]);
        // Integration name/alias is set to service ID by default
        $this->assertEquals('bar', $methodCalls[1][1][3]);
    }
}
