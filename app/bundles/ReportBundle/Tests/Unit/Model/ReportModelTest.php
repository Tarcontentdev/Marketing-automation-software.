<?php

declare(strict_types=1);

namespace MailVotech\ReportBundle\Tests\Unit\Model;

use MailVotech\CoreBundle\Entity\IpAddress;
use MailVotech\CoreBundle\Test\MailVotechMysqlTestCase;
use MailVotech\FormBundle\Entity\Form;
use MailVotech\FormBundle\Entity\Submission;
use MailVotech\ReportBundle\Entity\Report;
use MailVotech\ReportBundle\Model\ReportModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

final class ReportModelTest extends MailVotechMysqlTestCase
{
    public function testThatGetReportDataUsesCorrectDataRange(): void
    {
        $report = new Report();
        $report->setName('Test Report');
        $report->setSource('form.submissions');
        $report->setColumns(['fs.date_submitted']);
        $report->setSettings([]);

        $form = new Form();
        $form->setName('Test Form');
        $form->setAlias('create_a_c');

        $ip = new IpAddress('127.0.0.1');

        $this->em->persist($ip);
        $this->em->persist($report);
        $this->em->persist($form);
        $this->em->flush();

        // I know I can use \DateTimeImmutable, but getReportData expects \DateTime
        $now        = new \DateTime('now', new \DateTimeZone('UTC'));
        $aDayAgo    = (clone $now)->modify('-1 day');
        $twoDaysAgo = (clone $now)->modify('-2 days');

        $this->em->persist($this->makeSubmission($form, $ip, $twoDaysAgo));
        $this->em->persist($this->makeSubmission($form, $ip, $aDayAgo));
        $this->em->persist($this->makeSubmission($form, $ip, $now));

        $this->em->flush();

        $session = $this->createStub(Session::class);
        $request = new Request();
        $request->setSession($session);
        /** @var RequestStack $requestStack */
        $requestStack = self::getContainer()->get(RequestStack::class);
        $requestStack->push($request);
        /** @var ReportModel $reportModel */
        $reportModel = self::getContainer()->get(ReportModel::class);

        $aDayAgoBeginningOfTheDay = (clone $aDayAgo)->setTime(0, 0, 0);

        $reportData = $reportModel->getReportData($report, null, [
            'dateFrom' => $aDayAgoBeginningOfTheDay,
            'dateTo'   => clone $aDayAgoBeginningOfTheDay,
        ]);

        $this->assertSame(1, $reportData['totalResults']);
        $this->assertCount(1, $reportData['data']);
    }

    private function makeSubmission(Form $form, IpAddress $ipAddress, \DateTime $dateSubmitted): Submission
    {
        $submission = new Submission();
        $submission->setForm($form);
        $submission->setIpAddress($ipAddress);
        $submission->setDateSubmitted($dateSubmitted);
        $submission->setReferer('');

        return $submission;
    }
}
