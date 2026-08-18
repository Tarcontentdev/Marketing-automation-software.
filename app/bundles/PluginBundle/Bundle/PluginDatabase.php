<?php

declare(strict_types=1);

namespace MailVotech\PluginBundle\Bundle;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\SchemaTool;
use MailVotech\IntegrationsBundle\Migration\Engine;
use MailVotech\PluginBundle\Entity\Plugin;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class PluginDatabase
{
    private string $mailvotechDbPrefix;

    public function __construct(
        private EntityManagerInterface $em,
        private Connection $connection,
        #[Autowire(env: 'MAILVOTECH_TABLE_PREFIX')]
        ?string $mailvotechDbPrefix,
    ) {
        $this->mailvotechDbPrefix = $mailvotechDbPrefix ?? '';
    }

    /**
     * Install plugin schema based on Doctrine metadata.
     *
     * @param array<class-string, ClassMetadata> $metadata
     *
     * @throws \Exception
     */
    public function installPluginSchema(array $metadata, ?bool $installedSchema = null): void
    {
        if (null !== $installedSchema) {
            // Schema already exists, so no need to proceed
            return;
        }

        $schemaTool     = new SchemaTool($this->em);
        $installQueries = $schemaTool->getCreateSchemaSql(array_values($metadata));

        foreach ($installQueries as $q) {
            // Check if the query is a DDL statement
            if ($this->isDDLStatement($q)) {
                // Execute DDL statements outside of a transaction
                $this->connection->executeStatement($q);
            } else {
                // For non-DDL statements, use transactions
                try {
                    $this->connection->beginTransaction();
                    $this->connection->executeStatement($q);
                    $this->connection->commit();
                } catch (\Exception $e) {
                    // Rollback only for non-DDL statements
                    if ($this->connection->isTransactionActive()) {
                        $this->connection->rollBack();
                    }
                    throw $e;
                }
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function onPluginUpdate(Plugin $plugin): void
    {
        $migrationEngine = new Engine(
            $this->em,
            $this->mailvotechDbPrefix,
            __DIR__.'/../../../../plugins/'.$plugin->getBundle(),
            $plugin->getBundle()
        );

        $migrationEngine->up();
    }

    /**
     * @param array<int, ClassMetadata> $metadata
     */
    public function dropPluginSchema(array $metadata): void
    {
        $db          = $this->em->getConnection();
        $schemaTool  = new SchemaTool($this->em);
        $dropQueries = $schemaTool->getDropSchemaSQL($metadata);

        $db->beginTransaction();
        try {
            foreach ($dropQueries as $q) {
                $db->executeStatement($q);
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollback();

            throw $e;
        }
    }

    private function isDDLStatement(string $query): bool|int
    {
        return preg_match('/^(CREATE|ALTER|DROP|RENAME|TRUNCATE|COMMENT)\s/i', $query);
    }
}
