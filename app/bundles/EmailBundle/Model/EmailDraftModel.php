<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Model;

use MailVotech\CoreBundle\Model\AbstractCommonModel;
use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Entity\EmailDraft;
use MailVotech\EmailBundle\Entity\EmailDraftRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Service\Attribute\Required;

class EmailDraftModel extends AbstractCommonModel
{
    private EmailDraftRepository $emailDraftRepository;

    #[Required]
    public function autowireEmailDraftModel(
        EmailDraftRepository $emailDraftRepository,
    ): void {
        $this->emailDraftRepository = $emailDraftRepository;
    }

    public function createDraft(Email $email, string $html, string $template, bool $publicPreview = true): EmailDraft
    {
        $emailDraft = $this->emailDraftRepository->findOneBy(['email' => $email]);
        if (null !== $emailDraft) {
            throw new \Exception(sprintf('Draft already exists for email %d', $email->getId()));
        }
        $emailDraft = new EmailDraft($email, $html, $template, $publicPreview);

        $this->em->persist($emailDraft);
        $this->em->flush();

        return $emailDraft;
    }

    public function deleteDraft(Email $email): void
    {
        if (null === ($emailDraft = $email->getDraft())) {
            throw new NotFoundHttpException(sprintf('Draft not found for email %d', $email->getId()));
        }
        $this->em->remove($emailDraft);
        $this->em->flush();
    }

    public function getPermissionBase(): string
    {
        return 'email:emails';
    }

    public function getRepository(): EmailDraftRepository
    {
        return $this->emailDraftRepository;
    }
}
