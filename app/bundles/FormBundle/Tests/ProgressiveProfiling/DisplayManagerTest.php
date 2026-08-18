<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Tests\ProgressiveProfiling;

use MailVotech\FormBundle\Entity\Field;
use MailVotech\FormBundle\Entity\Form;
use MailVotech\FormBundle\ProgressiveProfiling\DisplayManager;

final class DisplayManagerTest extends \PHPUnit\Framework\TestCase
{
    public function testShowForField(): void
    {
        $form           = new Form();
        $viewOnlyFields = ['button'];
        $displayManager = new DisplayManager($form, $viewOnlyFields);
        $displayCounter = $displayManager->getDisplayCounter();

        $field = new Field();
        $this->assertTrue($displayManager->showForField($field));

        $field->setType('button');
        $this->assertTrue($displayManager->showForField($field));

        $field->setType('text');

        // display If first field is always display and progressive limit 1
        $field->setAlwaysDisplay(true);
        $form->setProgressiveProfilingLimit(1);
        $this->assertTrue($displayManager->showForField($field));

        // not display If second field is always display and progressive limit 1
        $displayCounter->increaseDisplayedFields();
        $this->assertFalse($displayManager->showForField($field));
    }
}
