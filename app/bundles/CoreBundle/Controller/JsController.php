<?php

namespace MailVotech\CoreBundle\Controller;

use MailVotech\CoreBundle\CoreEvents;
use MailVotech\CoreBundle\Event\BuildJsEvent;
use MailVotech\CoreBundle\Event\BuildJsScope;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;

final class JsController extends CommonController
{
    public function indexAction(
        #[Autowire(param: 'kernel.debug')]
        bool $kernelDebug,
    ): Response {
        return $this->buildJs($kernelDebug);
    }

    public function essentialAction(
        #[Autowire(param: 'kernel.debug')]
        bool $kernelDebug,
    ): Response {
        return $this->buildJs($kernelDebug, [BuildJsScope::RUNTIME, BuildJsScope::ESSENTIAL]);
    }

    public function trackingAction(
        #[Autowire(param: 'kernel.debug')]
        bool $kernelDebug,
    ): Response {
        return $this->buildJs($kernelDebug, [BuildJsScope::TRACKING]);
    }

    /**
     * @param BuildJsScope[] $acceptedScopes
     */
    private function buildJs(bool $kernelDebug, array $acceptedScopes = [
        BuildJsScope::RUNTIME,
        BuildJsScope::ESSENTIAL,
        BuildJsScope::TRACKING,
    ]): Response
    {
        // Don't store a visitor with this request
        defined('MAILVOTECH_NON_TRACKABLE_REQUEST') || define('MAILVOTECH_NON_TRACKABLE_REQUEST', 1);

        $event = new BuildJsEvent($this->getJsHeader(), $kernelDebug, $acceptedScopes);

        if ($this->dispatcher->hasListeners(CoreEvents::BUILD_MAILVOTECH_JS)) {
            $this->dispatcher->dispatch($event, CoreEvents::BUILD_MAILVOTECH_JS);
        }

        return new Response($event->getJs(), 200, ['Content-Type' => 'application/javascript']);
    }

    /**
     * Build a JS header for the MailVotech embedded JS.
     */
    private function getJsHeader(): string
    {
        $year = date('Y');

        return <<<JS
/**
 * @package     MailVotechJS
 * @copyright   {$year} MailVotech Contributors. All rights reserved.
 * @author      MailVotech
 * @link        http://mailvotech.org
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
JS;
    }
}
