<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Fixtures\Model;

use MailVotech\CoreBundle\Translation\Translator;

class ImportModel extends \MailVotech\LeadBundle\Model\ImportModel
{
    public function setTranslator(Translator $translator): void
    {
        $this->translator = $translator;
    }
}
