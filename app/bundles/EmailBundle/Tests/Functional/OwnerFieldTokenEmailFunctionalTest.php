<?php

declare(strict_types=1);

namespace MailVotech\EmailBundle\Tests\Functional;

use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\CoreBundle\Tests\Functional\UserEntityTrait;
use MailVotech\EmailBundle\Entity\Email;
use MailVotech\EmailBundle\Entity\Stat;
use MailVotech\EmailBundle\Entity\StatRepository;
use MailVotech\EmailBundle\Model\EmailModel;
use MailVotech\LeadBundle\Entity\Lead;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;

final class OwnerFieldTokenEmailFunctionalTest extends MailVotechMysqlTestCase
{
    use UserEntityTrait;

    public function testOwnerFieldTokensAreReplacedInSentEmailContent(): void
    {
        $role  = $this->createRole(sprintf('Owner Role %s', uniqid()));
        $owner = $this->createUser(
            sprintf('owner-%s@example.com', uniqid()),
            sprintf('owner-%s', uniqid()),
            'Contact',
            'Owner',
            $role
        );

        $lead = new Lead();
        $lead->setFirstname('Contact');
        $lead->setLastname('Receiver');
        $lead->setEmail(sprintf('contact-%s@example.com', uniqid()));
        $lead->setOwner($owner);

        $email = new Email();
        $email->setEmailType('list');
        $email->setName('Owner token email');
        $email->setSubject('Owner token email');
        $email->setCustomHtml(
            '<html><body>'
            .'Owner first name: {ownerfield=firstname} '
            .'Owner last name: {ownerfield=lastname} '
            .'Owner email: {ownerfield=email} '
            .'<a id="owner-profile-link" href="https://example.mailvotech/author/{ownerfield=firstname}/">Owner profile</a>'
            .'</body></html>'
        );

        $this->em->persist($lead);
        $this->em->persist($email);
        $this->em->flush();

        /** @var EmailModel $emailModel */
        $emailModel = self::getContainer()->get(EmailModel::class);
        $emailModel->sendEmail(
            $email,
            [
                [
                    'id'        => $lead->getId(),
                    'email'     => $lead->getEmail(),
                    'firstname' => $lead->getFirstname(),
                    'lastname'  => $lead->getLastname(),
                    'owner_id'  => $owner->getId(),
                ],
            ]
        );

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

        $crawler = $this->client->request(Request::METHOD_GET, '/email/view/'.$emailStat->getTrackingHash());
        $body    = $crawler->filter('body');

        // Remove injected tracking tags for stable assertions, but keep links for URL assertions.
        $body->filter('img,div')->each(function (Crawler $crawler): void {
            foreach ($crawler as $node) {
                $node->parentNode->removeChild($node);
            }
        });

        $content = $body->html();

        $this->assertStringContainsString('Owner first name: Contact', $content);
        $this->assertStringContainsString('Owner last name: Owner', $content);
        $this->assertStringContainsString('Owner email: '.$owner->getEmail(), $content);
        $this->assertStringNotContainsString('{ownerfield=', $content);

        $profileLinkHref = $crawler->filter('#owner-profile-link')->attr('href');
        $this->assertNotNull($profileLinkHref);
        $this->assertStringNotContainsString('{ownerfield=', $profileLinkHref);
        if (str_starts_with($profileLinkHref, 'https://example.mailvotech/author/')) {
            $this->assertSame('https://example.mailvotech/author/Contact/', $profileLinkHref);
        } else {
            $this->client->followRedirects(false);
            $this->client->request(Request::METHOD_GET, $profileLinkHref);
            $location = $this->client->getResponse()->headers->get('Location');
            $this->assertNotNull($location);
            $this->assertStringContainsString('https://example.mailvotech/author/Contact/', $location);
        }
    }
}
