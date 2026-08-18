<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\DependencyInjection\Compiler;

use GuzzleHttp\Handler\MockHandler;
use MailVotech\CoreBundle\Test\Guzzle\ClientFactory;
use MailVotech\CoreBundle\Test\PhpUnitConfigCommand;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class TestPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // Stub Guzzle HTTP client to prevent accidental request to third parties
        $definition = $container->getDefinition('mailvotech.http.client');
        $definition->setPublic(true)
            ->setFactory([ClientFactory::class, 'stub'])
            ->addArgument(new Reference(MockHandler::class));

        $container->removeAlias(HttpClientInterface::class);
        $container->register(MockHttpClient::class, MockHttpClient::class)->setAutowired(true)->setPublic(true);
        $container->setAlias(HttpClientInterface::class, MockHttpClient::class);
        $container->register(PhpUnitConfigCommand::class, PhpUnitConfigCommand::class)->addTag('console.command');
    }
}
