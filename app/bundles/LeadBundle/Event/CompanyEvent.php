<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Event;

use MailVotech\CoreBundle\Event\CommonEvent;
use MailVotech\LeadBundle\Entity\Company;

class CompanyEvent extends CommonEvent
{
    /**
     * @param bool $isNew
     * @param int  $score
     */
    public function __construct(
        Company $company,
        $isNew = false,
        protected $score = 0,
    ) {
        $this->entity = $company;
        $this->isNew  = $isNew;
    }

    /**
     * Returns the Company entity.
     *
     * @return Company
     */
    public function getCompany()
    {
        return $this->entity;
    }

    /**
     * Sets the Company entity.
     */
    public function setCompany(Company $company): void
    {
        $this->entity = $company;
    }

    public function changeScore($score): void
    {
        $this->score = $score;
    }

    public function getScore()
    {
        return $this->score;
    }
}
