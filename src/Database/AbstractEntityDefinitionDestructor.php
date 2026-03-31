<?php

declare(strict_types=1);

namespace NetInventors\Shopware6PluginInstaller\Database;

use Doctrine\DBAL\Connection;

abstract class AbstractEntityDefinitionDestructor implements EntityDefinitionDestructorInterface
{
    #[\Override]
    public function destruct(Connection $connection): void
    {
        $table = $this->getEntityDefinition()->getEntityName();

        $connection->executeStatement(
            'DROP TABLE IF EXISTS ' . $connection->getDatabasePlatform()->quoteSingleIdentifier($table)
        );
    }

    #[\Override]
    public function getDependencies(): array
    {
        return [];
    }
}
