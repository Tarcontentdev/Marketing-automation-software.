<?php

namespace MailVotech\PointBundle\Tests\Functional;

use MailVotech\PointBundle\Entity\Group;
use MailVotech\PointBundle\Entity\Trigger;
use MailVotech\PointBundle\Entity\TriggerEvent;

trait TriggerTrait
{
    private function createTrigger(
        string $name,
        int $points = 0,
        ?Group $group = null,
        bool $triggerExistingLeads = false,
    ): Trigger {
        $trigger = new Trigger();
        $trigger->setName($name);
        $trigger->setPoints($points);

        if ($group instanceof Group) {
            $trigger->setGroup($group);
        }
        if ($triggerExistingLeads) {
            $trigger->setTriggerExistingLeads($triggerExistingLeads);
        }
        $this->em->persist($trigger);

        return $trigger;
    }

    private function createAddTagEvent(
        string $tag,
        Trigger $trigger,
    ): TriggerEvent {
        $triggerEvent = new TriggerEvent();
        $triggerEvent->setTrigger($trigger);
        $triggerEvent->setName('Add '.$tag);
        $triggerEvent->setType('lead.changetags');
        $triggerEvent->setProperties([
            'add_tags'    => [$tag],
            'remove_tags' => [],
        ]);
        $this->em->persist($triggerEvent);

        return $triggerEvent;
    }
}
