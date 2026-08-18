<?php

use Doctrine\ORM\EntityManager;
use MailVotech\CoreBundle\ErrorHandler\ErrorHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;

define('MAILVOTECH_ROOT_DIR', __DIR__);

// Fix for hosts that do not have date.timezone set; it will be reset based on user settings.
date_default_timezone_set('UTC');

require_once __DIR__.'/../autoload.php';

ErrorHandler::register('prod');

$kernel = new AppKernel('prod', false);
$kernel->boot();

/** @var ContainerInterface $container */
$container = $kernel->getContainer();

/** @var EntityManager $objectManager */
$objectManager = $container->get('doctrine')->getManager();

return $objectManager;
