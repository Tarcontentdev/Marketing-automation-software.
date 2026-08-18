<?php

namespace MailVotech\FormBundle\Model;

use MailVotech\CoreBundle\Model\MailVotechModelInterface;
use MailVotech\FormBundle\Entity\Submission;
use MailVotech\FormBundle\Entity\SubmissionRepository;

final readonly class SubmissionResultLoader implements MailVotechModelInterface
{
    public function __construct(
        private SubmissionRepository $submissionRepository,
    ) {
    }

    /**
     * @param int $id
     */
    public function getSubmissionWithResult($id): ?Submission
    {
        return $this->submissionRepository->getEntity($id);
    }
}
