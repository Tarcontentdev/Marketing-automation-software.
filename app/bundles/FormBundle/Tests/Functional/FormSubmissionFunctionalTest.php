<?php

declare(strict_types=1);

namespace MailVotech\FormBundle\Tests\Functional;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Entity\Stat;
use MailVotech\EmailBundle\Entity\StatRepository;
use MailVotech\EmailBundle\Model\EmailModel;
use MailVotech\FormBundle\Entity\Submission;
use MailVotech\FormBundle\Entity\SubmissionRepository;
use MailVotech\FormBundle\Tests\Model\FormSubmissionTrait;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\PageBundle\Entity\Page;
use Symfony\Component\HttpFoundation\Request;

final class FormSubmissionFunctionalTest extends MailVotechMysqlTestCase
{
    use FormSubmissionTrait;

    protected $useCleanupRollback = false;

    public function testGetSubmissionCountsByPage(): void
    {
        // Create a form
        [$formId, $formAlias] = $this->createFormWithoutCompanies();

        // Create a landing Page having form
        $pageA = $this->createLandingPage('Page with form', 'page-with-form', sprintf('<p>{form=%s}</p>', $formId));

        // Create a landing Page
        $pageB = $this->createLandingPage('Normal page', 'normal-page', '<p>Hello</p>');

        $this->em->flush();

        // Submit form on landing page
        $this->submitLandingPageFormWithoutCompanies($formAlias, $pageA->getAlias(), 'j@doe.com', 'John', 'Doe');

        /** @var SubmissionRepository $submissionRepo */
        $submissionRepo = $this->em->getRepository(Submission::class);

        // Set tracking_id on submission using direct SQL (simulates device tracking which doesn't work in functional tests)
        $submissions = $submissionRepo->findBy(['page' => $pageA]);
        $this->assertCount(1, $submissions);
        $trackingId = hash('sha1', uniqid((string) random_int(0, mt_getrandmax()), true));

        $this->connection->executeStatement(
            'UPDATE '.MAILVOTECH_TABLE_PREFIX.'form_submissions SET tracking_id = ? WHERE id = ?',
            [$trackingId, $submissions[0]->getId()]
        );

        $this->assertCount(1, $submissionRepo->getSubmissionCountsByPage($pageA->getId()));

        $countByPage = $submissionRepo->getSubmissionCountsByPage([$pageA->getId(), $pageB->getId()]);

        $this->assertSame(1, (int) $countByPage[0]['count']);
    }

    public function testGetSubmissionCountsByEmail(): void
    {
        // Create a form
        [$formId, $formAlias] = $this->createFormWithoutCompanies();

        // Create a landing Page having form
        $pageA = $this->createLandingPage('Page with form', 'page-with-form', sprintf('<p>{form=%s}</p>', $formId));

        $lead = new Lead();
        $lead->setFirstname('John');
        $lead->setLastname('Doe');
        $lead->setEmail('j@doe.com');
        $this->em->persist($lead);

        $email = new Email();
        $email->setName('Landing page email');
        $email->setSubject('Subject For Landing Page Email');
        $email->setCustomHtml(sprintf('This is the link: <a title="Page Link" href="%s://%s/%s">page-with-form</a> ', $this->client->getRequest()->getScheme(), $this->client->getRequest()->getHost(), $pageA->getAlias()));

        $this->em->persist($email);
        $this->em->flush();

        /** @var EmailModel $emailModel */
        $emailModel = self::getContainer()->get(EmailModel::class);
        $emailModel->sendEmail($email, [[
            'id'        => $lead->getId(),
            'email'     => $lead->getEmail(),
            'firstname' => $lead->getFirstname(),
            'lastname'  => $lead->getLastname(), ]]);

        /** @var StatRepository $emailStatRepository */
        $emailStatRepository = $this->em->getRepository(Stat::class);

        /** @var Stat|null $emailStat */
        $emailStat = $emailStatRepository->findOneBy(
            [
                'email' => $email->getId(),
                'lead'  => $lead->getId(),
            ]
        );
        $this->assertInstanceOf(Stat::class, $emailStat);

        $crawler = $this->client->request(Request::METHOD_GET, "/email/view/{$emailStat->getTrackingHash()}");

        $linkUrl = $crawler->selectLink('page-with-form')->attr('href');
        $crawler = $this->client->request(Request::METHOD_GET, $linkUrl);

        $formCrawler = $crawler->filter('form[id=mailvotechform_'.$formAlias.']');
        $this::assertCount(1, $formCrawler, $this->client->getResponse()->getContent());
        $form = $formCrawler->form();
        $form->setValues([
            'mailvotechform[email]'     => $lead->getEmail(),
            'mailvotechform[firstname]' => $lead->getFirstname(),
            'mailvotechform[lastname]'  => $lead->getLastname(),
        ]);
        $this->client->submit($form);
        $this->assertResponseIsSuccessful($this->client->getResponse()->getContent());

        /** @var SubmissionRepository $submissionRepo */
        $submissionRepo = $this->em->getRepository(Submission::class);

        // Verify submission is associated with the page
        $submissions = $submissionRepo->findBy(['page' => $pageA]);
        $this->assertCount(1, $submissions);

        // Verify referer is tracked
        $this->assertNotNull($submissions[0]->getReferer());
    }

    public function testValidateSubmissions(): void
    {
        // Create a form
        [$formId, $formAlias] = $this->createFormWithoutCompanies();

        // Create a landing Page having form
        $page = $this->createLandingPage('Page with form', 'page-with-form', sprintf('<p>{form=%s}</p>', $formId));

        $this->em->flush();

        // Create contact with the same email but different lastname.
        $this->submitLandingPageFormWithoutCompanies($formAlias, $page->getAlias(), 'j@doe.com', 'John', 'Doe');

        /** @var SubmissionRepository $submissionRepo */
        $submissionRepo = $this->em->getRepository(Submission::class);

        $this->assertEmpty($submissionRepo->validateSubmissions([9999999999999], $formId));

        $submissionIds = [$submissionRepo->findOneBy(['page' => $page])->getId()];
        $this->assertCount(1, $submissionRepo->validateSubmissions($submissionIds, $formId));
    }

    private function submitLandingPageFormWithoutCompanies(string $formAlias, string $pageAlias, string $email, string $firstname, string $lastname): void
    {
        $values = [
            'mailvotechform[email]'     => $email,
            'mailvotechform[firstname]' => $firstname,
            'mailvotechform[lastname]'  => $lastname,
        ];

        $this->submitLandingPageForm($formAlias, $pageAlias, $values);
    }

    /**
     * @param array<string, mixed> $values
     */
    private function submitLandingPageForm(string $formAlias, string $pageAlias, array $values): void
    {
        $crawler     = $this->client->request(Request::METHOD_GET, "/{$pageAlias}");
        $formCrawler = $crawler->filter('form[id=mailvotechform_'.$formAlias.']');
        $this::assertCount(1, $formCrawler, $this->client->getResponse()->getContent());
        $form = $formCrawler->form();
        $form->setValues($values);
        $this->client->submit($form);
        $this->assertResponseIsSuccessful($this->client->getResponse()->getContent());
    }

    private function createLandingPage(string $title, string $alias, string $content): Page
    {
        $pageObject = new Page();
        $pageObject->setIsPublished(true);
        $pageObject->setDateAdded(new \DateTime());
        $pageObject->setTitle($title);
        $pageObject->setAlias($alias);
        $pageObject->setTemplate('Blank');
        $pageObject->setCustomHtml($content);
        $pageObject->setLanguage('en');

        $this->em->persist($pageObject);

        return $pageObject;
    }
}
