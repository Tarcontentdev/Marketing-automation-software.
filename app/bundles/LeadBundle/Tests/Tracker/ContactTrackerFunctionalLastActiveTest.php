<?php

declare(strict_types=1);

namespace MailVotech\LeadBundle\Tests\Tracker;

use MailVotech\CoreBundle\Entity\IpAddress;
use MailVotech\CoreBundle\Helper\ClickthroughHelper;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\EmailBundle\Entity\Stat;
use MailVotech\LeadBundle\Entity\Lead;
use MailVotech\LeadBundle\Event\LeadGetCurrentEvent;
use MailVotech\LeadBundle\Helper\ContactRequestHelper;
use MailVotech\PageBundle\Entity\Page;
use MailVotech\PageBundle\Model\PageModel;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ContactTrackerFunctionalLastActiveTest extends MailVotechMysqlTestCase
{
    protected function setUp(): void
    {
        $this->configParams['track_contact_by_ip'] = true;

        parent::setUp();
    }

    /**
     * @return iterable<string,array{bool}>
     */
    public static function dataSkipContactLastActiveLogged(): iterable
    {
        yield 'Skipping turned off' => [false];
        yield 'Skipping turned on' => [true];
    }

    #[DataProvider('dataSkipContactLastActiveLogged')]
    public function testSkipContactLastActiveLogged(bool $skip): void
    {
        $this->logoutUser();

        if ($skip) {
            $eventDispatcher = self::getContainer()->get(EventDispatcherInterface::class);
            $eventDispatcher->addListener(LeadGetCurrentEvent::class, function (LeadGetCurrentEvent $event): void {
                $event->skipContactLastActiveLogged();
            });
        }

        $ipAddress = new IpAddress('127.0.0.1');
        $this->em->persist($ipAddress);

        $lead = new Lead();
        $lead->setEmail('testemail@domain.tld');
        $lead->addIpAddress($ipAddress);
        $this->em->persist($lead);

        $stat = new Stat();
        $stat->setTrackingHash('62970e83798e0668813916');
        $stat->setDateSent(new \DateTime());
        $stat->setEmailAddress($lead->getEmail());
        $this->em->persist($stat);

        $page = new Page();
        $page->setTitle('Page');
        $page->setAlias('page');
        $page->setCustomHtml('<html><body>Content</body></html>');
        $this->em->persist($page);
        $this->em->flush();

        $this->assertFalse($this->isLastActiveDateSet($lead->getId()));

        $request = new Request([
            'ct' => ClickthroughHelper::encodeArrayForUrl([
                'source' => [],
                'email'  => null,
                'stat'   => $stat->getTrackingHash(),
                'lead'   => $lead->getId(),
            ]),
        ]);
        $pageModel       = self::getContainer()->get(PageModel::class);
        $trackerHelper   = self::getContainer()->get(ContactRequestHelper::class);
        $query           = $pageModel->getHitQuery($request, $page);
        $trackerHelper->getContactFromQuery($query);

        $this->assertSame(!$skip, $this->isLastActiveDateSet($lead->getId()));
    }

    private function isLastActiveDateSet(int $id): bool
    {
        return (bool) $this->em->getRepository(Lead::class)->createQueryBuilder('l')
            ->select('l.lastActive')
            ->where('l.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
