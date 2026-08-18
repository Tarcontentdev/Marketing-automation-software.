<?php

declare(strict_types=1);

namespace MailVotech\CategoryBundle\Event;

use MailVotech\CategoryBundle\Entity\Category;
use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\CoreBundle\Event\DependencyErrorEventInterface;
use MailVotech\CoreBundle\Event\DependencyErrorEventTrait;

final class CategoryEvent extends CommonEvent implements DependencyErrorEventInterface
{
    use DependencyErrorEventTrait;

    /**
     * @param bool $isNew
     */
    public function __construct(Category &$category, $isNew = false)
    {
        $this->entity = &$category;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Category entity.
     *
     * @return Category
     */
    public function getCategory()
    {
        return $this->entity;
    }

    /**
     * Sets the Category entity.
     */
    public function setCategory(Category $category): void
    {
        $this->entity = $category;
    }
}
