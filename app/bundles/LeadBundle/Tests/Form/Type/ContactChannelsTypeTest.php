<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Form\Type;

use MailVotech\CoreBundle\Test\AbstractMailVotechTestCase;
use MailVotech\LeadBundle\Form\Type\ContactChannelsType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

final class ContactChannelsTypeTest extends AbstractMailVotechTestCase
{
    protected function setUp(): void
    {
        $this->configParams['show_contact_pause_dates'] = true;
        parent::setUp();
    }

    public function testPauseDatesAreProperlyConfigured(): void
    {
        $form = $this->createForm(true);
        $this->assertOptions($form, 'contact_pause_start_date_email', true);
        $this->assertOptions($form, 'contact_pause_end_date_email', true);

        $form = $this->createForm(false);
        $this->assertOptions($form, 'contact_pause_start_date_email', false);
        $this->assertOptions($form, 'contact_pause_end_date_email', false);
    }

    /**
     * @param FormInterface<FormInterface<mixed>> $form
     */
    private function assertOptions(FormInterface $form, string $name, bool $hasHtml5): void
    {
        $config = $form->get($name)->getConfig();
        $this->assertSame($hasHtml5, $config->getOption('html5'));
        $this->assertSame('yyyy-MM-dd', $config->getOption('format'));
    }

    /**
     * @return FormInterface<FormInterface<mixed>>
     */
    private function createForm(bool $publicView): FormInterface
    {
        return self::getContainer()->get(FormFactoryInterface::class)->create(
            ContactChannelsType::class,
            null,
            [
                'channels'    => ['Email' => 'email'],
                'public_view' => $publicView,
            ]
        );
    }
}
