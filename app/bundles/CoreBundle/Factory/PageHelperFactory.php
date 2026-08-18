<?php

declare(strict_types=1);

namespace MailVotech\CoreBundle\Factory;

use MailVotech\CoreBundle\Helper\CoreParametersHelper;
use MailVotech\CoreBundle\Helper\PageHelper;
use MailVotech\CoreBundle\Helper\PageHelperInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class PageHelperFactory implements PageHelperFactoryInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private CoreParametersHelper $coreParametersHelper,
    ) {
    }

    public function make(string $sessionPrefix, int $page): PageHelperInterface
    {
        return new PageHelper($this->requestStack, $this->coreParametersHelper, $sessionPrefix, $page);
    }
}
