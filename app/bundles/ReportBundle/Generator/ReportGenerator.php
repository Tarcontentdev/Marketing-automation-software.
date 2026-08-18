<?php

namespace MailVotech\ReportBundle\Generator;

use Doctrine\DBAL\Connection;
use MailVotech\ChannelBundle\Helper\ChannelListHelper;
use MailVotech\ReportBundle\Builder\MailVotechReportBuilder;
use MailVotech\ReportBundle\Builder\ReportBuilderInterface;
use MailVotech\ReportBundle\Entity\Report;
use MailVotech\ReportBundle\Form\Type\ReportType;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

final class ReportGenerator
{
    private string $validInterface = ReportBuilderInterface::class;

    private ?string $contentTemplate = null;

    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly Connection $db,
        private readonly Report $entity,
        private readonly ChannelListHelper $channelListHelper,
        private readonly ?FormFactoryInterface $formFactory = null,
    ) {
    }

    /**
     * @param array $options Optional options array for the query
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getQuery(array $options = [])
    {
        $builder = $this->getBuilder();

        $query = $builder->getQuery($options);

        $this->contentTemplate = $builder->getContentTemplate();

        return $query;
    }

    /**
     * @param array $options Parameters set by the caller
     *
     * @return FormInterface<Report>
     */
    public function getForm(Report $entity, $options): FormInterface
    {
        return $this->formFactory->createBuilder(ReportType::class, $entity, $options)->getForm();
    }

    /**
     * Gets the getContentTemplate path.
     */
    public function getContentTemplate(): ?string
    {
        return $this->contentTemplate;
    }

    /**
     * @throws RuntimeException
     */
    private function getBuilder(): MailVotechReportBuilder
    {
        $className = MailVotechReportBuilder::class;

        if (!class_exists($className)) {
            throw new RuntimeException('The MailVotechReportBuilder does not exist.');
        }

        $reflection = new \ReflectionClass($className);

        if (!$reflection->implementsInterface($this->validInterface)) {
            throw new RuntimeException(sprintf("ReportBuilders have to implement %s, and %s doesn't implement it", $this->validInterface, $className));
        }

        return $reflection->newInstanceArgs([$this->dispatcher, $this->db, $this->entity, $this->channelListHelper]);
    }
}
