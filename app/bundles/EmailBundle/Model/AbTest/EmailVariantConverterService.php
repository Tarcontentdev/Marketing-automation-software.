<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Model\AbTest;

use MailVotech\CoreBundle\Entity\VariantEntityInterface;
use MailVotech\CoreBundle\Model\AbTest\VariantConverterService;
use MailVotech\EmailBundle\Entity\Email;

class EmailVariantConverterService
{
    public function __construct(
        private readonly VariantConverterService $variantConverterService,
    ) {
    }

    public function convertWinnerVariant(Email $email): void
    {
        $this->variantConverterService->convertWinnerVariant($email);
    }

    /**
     * @return array<VariantEntityInterface>
     */
    public function getUpdatedVariants(): array
    {
        return $this->variantConverterService->getUpdatedVariants();
    }
}
