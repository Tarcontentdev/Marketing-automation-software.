<?php

declare(strict_types=1);

namespace MailVotech\NotificationBundle\Controller;

use MailVotech\CoreBundle\Controller\AjaxController as CommonAjaxController;
use MailVotech\CoreBundle\Controller\AjaxLookupControllerTrait;

final class AjaxController extends CommonAjaxController
{
    use AjaxLookupControllerTrait;
}
