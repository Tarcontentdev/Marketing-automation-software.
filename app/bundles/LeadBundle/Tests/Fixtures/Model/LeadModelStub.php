<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Fixtures\Model;

use MailVotech\CoreBundle\Helper\UserHelper;

class LeadModelStub extends \MailVotech\LeadBundle\Model\LeadModel
{
    public function setUserHelper(UserHelper $userHelper): void
    {
        $this->userHelper = $userHelper;
    }
}
