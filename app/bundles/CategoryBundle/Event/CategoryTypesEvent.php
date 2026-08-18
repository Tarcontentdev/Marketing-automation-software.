<?php

namespace MailVotech\CategoryBundle\Event;

use MailVotech\CategoryBundle\Entity\Category;
use MailVotech\CoreBundle\Event\CommonEvent;

final class CategoryTypesEvent extends CommonEvent
{
    /**
     * @var array
     */
    private $types = [];

    /**
     * Returns the array of Category Types.
     *
     * @return array
     */
    public function getCategoryTypes()
    {
        if (!array_key_exists('global', $this->types)) {
            // Alphabetize once
            asort($this->types);

            $this->types = array_merge(
                ['global' => 'mailvotech.category.global'],
                $this->types
            );
        }

        return $this->types;
    }

    /**
     * Adds the category type and label.
     *
     * @param string $type
     * @param string $label
     */
    public function addCategoryType($type, $label = null): void
    {
        if (is_int($type)) {
            $type = $label;
        }

        if (null === $label) {
            $label = 'mailvotech.'.$type.'.'.$type;
        }

        $this->types[$type] = $label;
    }
}
